import pandas as pd
import shutil
import os
from utils.cleaning_utils import limpiar_dataframe

def process_txt(ruta_archivo, historial_folder):
    df = pd.read_csv(ruta_archivo, sep=None, engine='python', na_values=["<NA>", "nan", "NaN", ""])
    df_mejorado = limpiar_dataframe(df)
    shutil.copy(ruta_archivo, os.path.join(historial_folder, os.path.basename(ruta_archivo)))
    return df_mejorado
