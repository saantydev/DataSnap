import pandas as pd
from pandas.errors import ParserError
import shutil
import os
from utils.cleaning_utils import limpiar_dataframe

def corregir_csv_mal_formado(ruta_original, ruta_temporal="archivo_corregido.csv", columnas_esperadas=5):
    with open(ruta_original, "r", encoding="utf-8") as original, open(ruta_temporal, "w", encoding="utf-8") as corregido:
        for linea in original:
            partes = linea.strip().split(",")
            if len(partes) > columnas_esperadas:
                partes = partes[:columnas_esperadas]
            if len(partes) == columnas_esperadas:
                corregido.write(",".join(partes) + "\n")
    return ruta_temporal

def process_csv(ruta_archivo, historial_folder):
    try:
        df = pd.read_csv(ruta_archivo, na_values=["<NA>", "nan", "NaN", ""])
    except ParserError:
        ruta_archivo = corregir_csv_mal_formado(ruta_archivo)
        df = pd.read_csv(ruta_archivo, na_values=["<NA>", "nan", "NaN", ""])
    df_mejorado = limpiar_dataframe(df)
    shutil.copy(ruta_archivo, os.path.join(historial_folder, os.path.basename(ruta_archivo)))
    return df_mejorado
