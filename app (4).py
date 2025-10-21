from flask import Flask, request, jsonify, send_file
import os, json, mysql.connector, requests, time
from parsers.csv_parser import process_csv
from parsers.txt_parser import process_txt
from parsers.xlsx_parser import process_xlsx
from parsers.json_parser import process_json
from google.oauth2 import service_account
from googleapiclient.discovery import build
from googleapiclient.http import MediaFileUpload

app = Flask(__name__)

UPLOAD_FOLDER = 'uploads'
PROCESSED_FOLDER = 'processed'
HISTORIAL_FOLDER = 'historial'
os.makedirs(UPLOAD_FOLDER, exist_ok=True)
os.makedirs(PROCESSED_FOLDER, exist_ok=True)
os.makedirs(HISTORIAL_FOLDER, exist_ok=True)

# Config DB desde variables de entorno
DB_CONFIG = {
    "host": os.environ.get("DB_HOST"),
    "user": os.environ.get("DB_USER"),
    "password": os.environ.get("DB_PASS"),
    "database": os.environ.get("DB_NAME"),
    "port": int(os.environ.get("DB_PORT", 3306))
}

# Config Google Drive
creds_info = json.loads(os.environ["GCP_SERVICE_ACCOUNT_JSON"])
creds = service_account.Credentials.from_service_account_info(
    creds_info,
    scopes=["https://www.googleapis.com/auth/drive"]
)
drive_service = build("drive", "v3", credentials=creds)
DRIVE_FOLDER_ID = os.environ["GDRIVE_FOLDER_ID"]

def execute_with_retry(request):
    max_retries = 5
    for attempt in range(max_retries):
        try:
            return request.execute()
        except Exception as e:
            if 'quotaExceeded' in str(e) or 'quota_exceeded' in str(e).lower():
                wait_time = 2 ** attempt  # exponential backoff
                time.sleep(wait_time)
                continue
            else:
                raise
    raise Exception("Max retries exceeded for Google Drive API quota")

@app.route('/upload_original', methods=['POST'])
def upload_original():
    if 'file' not in request.files:
        return jsonify({"success": False, "error": "No se envió archivo"}), 400

    file = request.files['file']
    local_path = os.path.join(UPLOAD_FOLDER, file.filename)
    file.save(local_path)

    try:
        file_metadata = {"name": file.filename, "parents": [DRIVE_FOLDER_ID]}
        media = MediaFileUpload(local_path, mimetype="application/octet-stream")
        uploaded = execute_with_retry(drive_service.files().create(
            body=file_metadata,
            media_body=media,
            fields="id, webViewLink"
        ))

        drive_id = uploaded["id"]
        drive_link = uploaded["webViewLink"]

        # Hacerlo accesible públicamente
        execute_with_retry(drive_service.permissions().create(
            fileId=drive_id,
            body={"type": "anyone", "role": "reader"}
        ))

        return jsonify({"success": True, "drive_id": drive_id, "drive_link": drive_link})
    except Exception as e:
        return jsonify({"success": False, "error": str(e)})

@app.route('/upload', methods=['POST'])
def upload():
    if 'file' not in request.files:
        return jsonify({"error": "No se envió archivo"}), 400
    file = request.files['file']
    save_path = os.path.join(UPLOAD_FOLDER, file.filename)
    file.save(save_path)
    return jsonify({"success": True, "ruta": save_path})

@app.route('/procesar', methods=['POST'])
def procesar():
    data = request.json
    if not data or 'id' not in data:
        return jsonify({"error": "No se envió el ID del archivo"}), 400

    id_archivo = data['id']

    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT ruta, nombre, drive_id_original FROM archivos WHERE id = %s", (id_archivo,))
        result = cursor.fetchone()
        conn.close()
        if not result:
            return jsonify({"error": "Archivo no encontrado en la base de datos"}), 404

        ruta = result['ruta']
        temp_file = False

        if result.get('drive_id_original'):
            # Descargar desde Google Drive
            download_url = f"https://drive.google.com/uc?export=download&id={result['drive_id_original']}"
            response = requests.get(download_url)
            if response.status_code == 200:
                temp_path = os.path.join(UPLOAD_FOLDER, f"temp_{id_archivo}_{result['nombre']}")
                with open(temp_path, 'wb') as f:
                    f.write(response.content)
                ruta = temp_path
                temp_file = True
            else:
                return jsonify({"error": "No se pudo descargar el archivo desde Google Drive"}), 500
        else:
            if not os.path.exists(ruta):
                return jsonify({"error": f"No se encontró el archivo: {ruta}"}), 404
    except Exception as e:
        return jsonify({"error": f"Error al conectar con la base de datos: {e}"}), 500

    extension = os.path.splitext(ruta)[1].lower()
    try:
        if extension == ".csv":
            df = process_csv(ruta, HISTORIAL_FOLDER)
        elif extension == ".txt":
            df = process_txt(ruta, HISTORIAL_FOLDER)
        elif extension == ".xlsx":
            df = process_xlsx(ruta, HISTORIAL_FOLDER)
        elif extension == ".json":
            df = process_json(ruta, HISTORIAL_FOLDER)
        else:
            return jsonify({"error": "Formato no soportado"}), 400
    except Exception as e:
        return jsonify({"error": f"Error al procesar: {e}"}), 500

    salida = os.path.join(PROCESSED_FOLDER, f"mejorado_{os.path.basename(ruta)}.csv")
    df.to_csv(salida, index=False, na_rep="NaN")

    # Limpiar archivo temporal si se descargó
    if temp_file:
        os.remove(ruta)

    # Subir a Google Drive
    try:
        file_metadata = {
            "name": os.path.basename(salida),
            "parents": [DRIVE_FOLDER_ID]
        }
        media = MediaFileUpload(salida, mimetype="text/csv")
        uploaded = execute_with_retry(drive_service.files().create(
            body=file_metadata,
            media_body=media,
            fields="id, webViewLink, webContentLink"
        ))

        drive_id = uploaded.get("id")
        drive_link = uploaded.get("webViewLink")

        # Hacer accesible por link público (opcional, quita si no querés compartirlo abiertamente)
        execute_with_retry(drive_service.permissions().create(
            fileId=drive_id,
            body={"type": "anyone", "role": "reader"},
        ))

    except Exception as e:
        return jsonify({"error": f"No se pudo subir a Google Drive: {e}"}), 500

    # Actualizar base de datos
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        cursor.execute("""
            UPDATE archivos
            SET estado = 'optimizado',
                drive_id = %s,
                drive_link = %s
            WHERE id = %s
        """, (drive_id, drive_link, id_archivo))
        conn.commit()
        conn.close()
    except Exception as e:
        return jsonify({"error": f"No se pudo actualizar la base de datos: {e}"}), 500

    return jsonify({
        "success": True,
        "archivo_id": id_archivo,
        "ruta_local": salida,
        "drive_id": drive_id,
        "drive_link": drive_link
    })

@app.route('/procesar_drive', methods=['POST'])
def procesar_drive():
    data = request.json
    if not data or 'drive_file_id' not in data:
        return jsonify({"error": "No se envió el ID del archivo de Google Drive"}), 400

    drive_file_id = data['drive_file_id']

    try:
        # Download from Drive
        request = drive_service.files().get_media(fileId=drive_file_id)
        temp_path = os.path.join(UPLOAD_FOLDER, f"temp_drive_{drive_file_id}")
        with open(temp_path, 'wb') as f:
            f.write(execute_with_retry(request))

        # Get file name
        file_info = execute_with_retry(drive_service.files().get(fileId=drive_file_id, fields="name"))
        nombre = file_info['name']
        ruta = temp_path
        temp_file = True
    except Exception as e:
        return jsonify({"error": f"Error al descargar desde Google Drive: {e}"}), 500

    extension = os.path.splitext(nombre)[1].lower()
    try:
        if extension == ".csv":
            df = process_csv(ruta, HISTORIAL_FOLDER)
        elif extension == ".txt":
            df = process_txt(ruta, HISTORIAL_FOLDER)
        elif extension == ".xlsx":
            df = process_xlsx(ruta, HISTORIAL_FOLDER)
        elif extension == ".json":
            df = process_json(ruta, HISTORIAL_FOLDER)
        else:
            if temp_file:
                os.remove(ruta)
            return jsonify({"error": "Formato no soportado"}), 400
    except Exception as e:
        if temp_file:
            os.remove(ruta)
        return jsonify({"error": f"Error al procesar: {e}"}), 500

    salida = os.path.join(PROCESSED_FOLDER, f"mejorado_{nombre}")
    df.to_csv(salida, index=False, na_rep="NaN")

    if temp_file:
        os.remove(ruta)

    # Upload to Drive
    try:
        file_metadata = {"name": os.path.basename(salida), "parents": [DRIVE_FOLDER_ID]}
        media = MediaFileUpload(salida, mimetype="text/csv")
        uploaded = execute_with_retry(drive_service.files().create(body=file_metadata, media_body=media, fields="id, webViewLink"))

        drive_id = uploaded["id"]
        drive_link = uploaded["webViewLink"]

        execute_with_retry(drive_service.permissions().create(fileId=drive_id, body={"type": "anyone", "role": "reader"}))

    except Exception as e:
        return jsonify({"error": f"No se pudo subir a Google Drive: {e}"}), 500

    return jsonify({"success": True, "ruta_local": salida, "drive_id": drive_id, "drive_link": drive_link})

if __name__ == '__main__':
    app.run(host="0.0.0.0", port=5000)
