import pandas as pd
from number_parser import parse_number, parser

parser._LANGUAGE = "es"

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

def reemplazar_vacios_con_nan(df):
    for col in df.columns:
        df[col] = df[col].replace(["", " ", "  "], pd.NA)
        if df[col].dtype == 'object':
            df[col] = df[col].astype(str).str.strip()
            df[col] = df[col].replace(r'^\s*$', pd.NA, regex=True)
    return df

def limpiar_dataframe(df):
    df = reemplazar_vacios_con_nan(df)
    df = df.drop_duplicates()
    df = df.dropna(how='all')
    for col in df.select_dtypes(include='object').columns:
        df[col] = df[col].astype(str).str.strip()
    df = convertir_columnas_a_numerico(df)
    return df
