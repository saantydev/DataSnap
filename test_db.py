import mysql.connector

config = {
    'host': 'escuelarobertoarlt.com',
    'dbname': 'u214138677_datasnap',
    'username': 'u214138677.datasnap',
    'password': 'Rasa@25ChrSt',
    'charset': 'utf8mb4'
}

try:
    conn = mysql.connector.connect(**config)
    print("Connection successful")
    conn.close()
except mysql.connector.Error as err:
    print(f"Error: {err}")