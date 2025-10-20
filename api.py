from flask import Flask, request, send_file, render_template_string
import pandas as pd
import os
import shutil
from number_parser import parse_number
from number_parser import parser
from pandas.errors import ParserError

# Configuración de Flask
app = Flask(__name__)

UPLOAD_FOLDER = 'uploads'
PROCESSED_FOLDER = 'processed'
HISTORIAL_FOLDER = 'historial'
os.makedirs(UPLOAD_FOLDER, exist_ok=True)
os.makedirs(PROCESSED_FOLDER, exist_ok=True)
os.makedirs(HISTORIAL_FOLDER, exist_ok=True)

parser._LANGUAGE = "es"

# HTML simple
HTML_FORM = '''
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>DataSnap - Subir Archivo</title>
    <link rel="stylesheet" href="{{ url_for('static', filename='css/panel.css') }}">
</head>
<div class="panel">
  <h2>Subí tu archivo para procesarlo con DataSnap</h2>
  <form action="/procesar" method="post" enctype="multipart/form-data">
      <input type="file" name="archivo" accept=".csv,.txt,.json,.xlsx,.sql" required>
      <button type="submit">Procesar</button>
  </form>
</div>

</html>
'''

# -------------------------------
# FUNCIONES DE LIMPIEZa
# -------------------------------
def convertir_columnas_a_numerico(df):
    columnas_sospechosas = [col for col in df.columns if any(pal in col.lower() for pal in ["edad", "número", "cantidad", "precio"])]

    for col in columnas_sospechosas:
        if df[col].dtype == 'object':
            df[col] = df[col].apply(lambda x: parse_number(str(x).strip()) if isinstance(x, str) else x)
            df[col] = pd.to_numeric(df[col], errors='coerce')

        if pd.api.types.is_float_dtype(df[col]):
            if (df[col].dropna() % 1 == 0).all():
                df[col] = df[col].astype('Int64') 
    return df

def corregir_csv_mal_formado(ruta_original, ruta_temporal="archivo_corregido.csv", columnas_esperadas=5):
    with open(ruta_original, "r", encoding="utf-8") as original, open(ruta_temporal, "w", encoding="utf-8") as corregido:
        for linea in original:
            partes = linea.strip().split(",")
            if len(partes) > columnas_esperadas:
                partes = partes[:columnas_esperadas]  
            if len(partes) == columnas_esperadas:
                corregido.write(",".join(partes) + "\n")
    return ruta_temporal

def reemplazar_vacios_con_nan(df):
    for col in df.columns:
        df[col] = df[col].replace(["", " ", "  "], pd.NA)
        if df[col].dtype == 'object':
            df[col] = df[col].astype(str).str.strip()
            df[col] = df[col].replace(r'^\s*$', pd.NA, regex=True)
    return df

def limpiar_y_mejorar(archivo_path):
    try:
        df = pd.read_csv(archivo_path, na_values=["<NA>", "nan", "NaN", ""])
    except ParserError:
        archivo_path = corregir_csv_mal_formado(archivo_path)
        df = pd.read_csv(archivo_path, na_values=["<NA>", "nan", "NaN", ""])

    df_mejorado = df.copy()
    df_mejorado = reemplazar_vacios_con_nan(df_mejorado)
    df_mejorado = df_mejorado.drop_duplicates()
    df_mejorado = df_mejorado.dropna(how='all')

    for col in df_mejorado.select_dtypes(include='object').columns:
        df_mejorado[col] = df_mejorado[col].astype(str).str.strip()

    df_mejorado = convertir_columnas_a_numerico(df_mejorado)

    # Guardar historial
    shutil.copy(archivo_path, os.path.join(HISTORIAL_FOLDER, os.path.basename(archivo_path)))

    return df_mejorado

# -------------------------------
# RUTAS FLASK
# -------------------------------
@app.route('/')
def formulario():
    return render_template_string(HTML_FORM)

@app.route('/procesar', methods=['POST'])
def procesar():
    if 'archivo' not in request.files:
        return "No se envió ningún archivo", 400

    archivo = request.files['archivo']
    if archivo.filename == '':
        return "Nombre de archivo inválido", 400

    # Guardar archivo original
    ruta_entrada = os.path.join(UPLOAD_FOLDER, archivo.filename)
    archivo.save(ruta_entrada)

    try:
        df_mejorado = limpiar_y_mejorar(ruta_entrada)
    except Exception as e:
        return f"Error al procesar el archivo: {e}", 500

    # Guardar archivo mejorado
    salida = os.path.join(PROCESSED_FOLDER, "mejorado_" + archivo.filename)
    df_mejorado.to_csv(salida, index=False, na_rep="NaN")

    return send_file(salida, as_attachment=True)

if __name__ == '__main__':
    app.run(host="0.0.0.0", port=5000)