import pandas as pd
import numpy as np
import re
from typing import Dict, List, Tuple, Any
from datetime import datetime

class UniversalDataOptimizer:
    """Optimizador universal que funciona con cualquier tipo de datos"""
    
    def __init__(self):
        # Patrones universales de detección
        self.column_patterns = {
            'email': ['email', 'mail', 'correo', 'e-mail', '@'],
            'phone': ['telefono', 'phone', 'tel', 'celular', 'movil'],
            'name': ['nombre', 'name', 'usuario', 'cliente', 'persona', 'apellido'],
            'price': ['precio', 'price', 'cost', 'valor', 'monto', 'importe', 'total'],
            'quantity': ['cantidad', 'qty', 'stock', 'inventario', 'unidades'],
            'date': ['fecha', 'date', 'time', 'año', 'mes', 'dia'],
            'boolean': ['activo', 'active', 'enabled', 'status', 'estado', 'vigente'],
            'category': ['categoria', 'tipo', 'class', 'grupo', 'seccion'],
            'id': ['id', 'codigo', 'key', 'clave', 'numero'],
            'address': ['direccion', 'address', 'ubicacion', 'domicilio'],
            'description': ['descripcion', 'desc', 'detalle', 'comentario', 'nota']
        }
        
        # Patrones de corrección universales
        self.universal_corrections = {
            'email_domains': {
                r'gmai\.com?$': 'gmail.com',
                r'hotmial?\.com?$': 'hotmail.com',
                r'yahoo\.co$': 'yahoo.com',
                r'outlok\.com$': 'outlook.com',
                r'@gmail$': '@gmail.com',
                r'@hotmail$': '@hotmail.com'
            },
            'boolean_values': {
                'si': 'si', 'sí': 'si', 'yes': 'si', 'true': 'si', '1': 'si', 'verdadero': 'si',
                'activo': 'si', 'enabled': 'si', 'on': 'si', 'vigente': 'si',
                'no': 'no', 'false': 'no', '0': 'no', 'inactivo': 'no', 'disabled': 'no',
                'off': 'no', 'vencido': 'no'
            }
        }
    
    def optimize_universal_data(self, df: pd.DataFrame) -> Tuple[pd.DataFrame, List[str]]:
        """Optimiza cualquier tipo de datos automáticamente"""
        improvements = []
        df_clean = df.copy()
        
        # 1. Análisis automático de columnas
        column_types = self._analyze_columns(df_clean)
        improvements.append(f"Analizadas {len(column_types)} columnas automáticamente")
        
        # 2. Limpieza universal de espacios y caracteres
        improvements.extend(self._universal_text_cleaning(df_clean))
        
        # 3. Optimización por tipo detectado
        for col, detected_type in column_types.items():
            if detected_type == 'email':
                improvements.extend(self._optimize_email_column(df_clean, col))
            elif detected_type == 'phone':
                improvements.extend(self._optimize_phone_column(df_clean, col))
            elif detected_type == 'name':
                improvements.extend(self._optimize_name_column(df_clean, col))
            elif detected_type == 'price':
                improvements.extend(self._optimize_price_column(df_clean, col))
            elif detected_type == 'quantity':
                improvements.extend(self._optimize_quantity_column(df_clean, col))
            elif detected_type == 'date':
                improvements.extend(self._optimize_date_column(df_clean, col))
            elif detected_type == 'boolean':
                improvements.extend(self._optimize_boolean_column(df_clean, col))
            elif detected_type == 'category':
                improvements.extend(self._optimize_category_column(df_clean, col))
            elif detected_type == 'address':
                improvements.extend(self._optimize_address_column(df_clean, col))
        
        # 4. Limpieza universal de valores nulos
        improvements.extend(self._universal_null_cleaning(df_clean))
        
        # 5. Optimización de tipos de datos
        improvements.extend(self._optimize_data_types(df_clean))
        
        # 6. Generación inteligente de datos faltantes
        improvements.extend(self._generate_missing_data(df_clean, column_types))
        
        return df_clean, improvements
    
    def _analyze_columns(self, df: pd.DataFrame) -> Dict[str, str]:
        """Detecta automáticamente el tipo de cada columna"""
        column_types = {}
        
        for col in df.columns:
            col_lower = col.lower()
            sample_data = df[col].dropna().astype(str).head(10)
            
            # Detectar por nombre de columna
            detected = False
            for data_type, patterns in self.column_patterns.items():
                if any(pattern in col_lower for pattern in patterns):
                    column_types[col] = data_type
                    detected = True
                    break
            
            if not detected:
                # Detectar por contenido
                if sample_data.str.contains('@').any():
                    column_types[col] = 'email'
                elif sample_data.str.match(r'^\+?\d[\d\s\-\(\)]+$').any():
                    column_types[col] = 'phone'
                elif sample_data.str.match(r'^\d{4}-\d{2}-\d{2}|\d{2}/\d{2}/\d{4}').any():
                    column_types[col] = 'date'
                elif pd.to_numeric(sample_data, errors='coerce').notna().sum() > len(sample_data) * 0.7:
                    if sample_data.astype(str).str.contains(r'[\.\\,]\d{2}$').any():
                        column_types[col] = 'price'
                    else:
                        column_types[col] = 'quantity'
                elif sample_data.str.lower().isin(['si', 'no', 'true', 'false', '1', '0']).any():
                    column_types[col] = 'boolean'
                else:
                    column_types[col] = 'text'
        
        return column_types
    
    def _universal_text_cleaning(self, df: pd.DataFrame) -> List[str]:
        """Limpieza universal de texto"""
        improvements = []
        text_cols = df.select_dtypes(include=['object']).columns
        
        if len(text_cols) > 0:
            # Limpiar espacios extra
            df[text_cols] = df[text_cols].astype(str).apply(lambda x: x.str.strip())
            
            # Remover caracteres especiales problemáticos
            df[text_cols] = df[text_cols].apply(lambda x: x.str.replace(r'[^\w\s@\.\-\+]', '', regex=True))
            
            improvements.append(f"Limpieza universal aplicada a {len(text_cols)} columnas de texto")
        
        return improvements
    
    def _optimize_email_column(self, df: pd.DataFrame, col: str) -> List[str]:
        """Optimiza columnas de email universalmente"""
        improvements = []
        original = df[col].copy()
        
        # Convertir a minúsculas
        df[col] = df[col].astype(str).str.lower()
        
        # Aplicar correcciones de dominios
        for pattern, replacement in self.universal_corrections['email_domains'].items():
            df[col] = df[col].str.replace(pattern, replacement, regex=True)
        
        # Completar emails incompletos
        incomplete_mask = ~df[col].str.contains('@', na=False) & (df[col] != 'nan')
        df.loc[incomplete_mask, col] = df.loc[incomplete_mask, col] + '@gmail.com'
        
        # Corregir emails inválidos usando otros datos
        self._fix_invalid_emails(df, col)
        
        changes = (original.astype(str) != df[col].astype(str)).sum()
        if changes > 0:
            improvements.append(f"Optimizados {changes} emails en '{col}'")
        
        return improvements
    
    def _optimize_phone_column(self, df: pd.DataFrame, col: str) -> List[str]:
        """Optimiza números de teléfono"""
        improvements = []
        original = df[col].copy()
        
        # Limpiar y formatear teléfonos
        df[col] = df[col].astype(str).str.replace(r'[^\d\+]', '', regex=True)
        
        # Agregar código de país si falta
        mask = df[col].str.match(r'^\d{9}$', na=False)
        df.loc[mask, col] = '+34' + df.loc[mask, col]
        
        changes = (original.astype(str) != df[col].astype(str)).sum()
        if changes > 0:
            improvements.append(f"Formateados {changes} teléfonos en '{col}'")
        
        return improvements
    
    def _optimize_name_column(self, df: pd.DataFrame, col: str) -> List[str]:
        """Optimiza nombres universalmente"""
        improvements = []
        original = df[col].copy()
        
        # Capitalizar nombres
        df[col] = df[col].str.title()
        
        # Generar nombres faltantes
        missing_mask = df[col].isna() | (df[col].astype(str).isin(['', 'nan', 'Nan']))
        
        if missing_mask.any():
            # Buscar columna de email para extraer nombres
            email_cols = [c for c in df.columns if any(p in c.lower() for p in ['email', 'mail'])]
            if email_cols:
                for idx in df[missing_mask].index:
                    email = df.loc[idx, email_cols[0]]
                    if pd.notna(email) and '@' in str(email):
                        name = str(email).split('@')[0].replace('.', ' ').title()
                        df.loc[idx, col] = name
            
            # Para los que aún faltan, usar ID o genérico
            still_missing = df[col].isna() | (df[col].astype(str).isin(['', 'nan', 'Nan']))
            if 'id' in df.columns:
                for idx in df[still_missing].index:
                    df.loc[idx, col] = f"Usuario{df.loc[idx, 'id']}"
            else:
                df.loc[still_missing, col] = 'Usuario'
        
        changes = (original.astype(str) != df[col].astype(str)).sum()
        if changes > 0:
            improvements.append(f"Optimizados {changes} nombres en '{col}'")
        
        return improvements
    
    def _optimize_price_column(self, df: pd.DataFrame, col: str) -> List[str]:
        """Optimiza precios universalmente"""
        improvements = []
        
        # Convertir a numérico
        df[col] = pd.to_numeric(df[col], errors='coerce')
        
        # Corregir valores negativos o extremos
        median_price = df[col].median()
        if pd.isna(median_price):
            median_price = 100.0
        
        # Reemplazar valores problemáticos
        problem_mask = (df[col] < 0) | (df[col] > median_price * 100) | df[col].isna()
        df.loc[problem_mask, col] = median_price
        
        improvements.append(f"Precios optimizados en '{col}' (mediana: {median_price:.2f})")
        return improvements
    
    def _optimize_quantity_column(self, df: pd.DataFrame, col: str) -> List[str]:
        """Optimiza cantidades/stock"""
        improvements = []
        
        df[col] = pd.to_numeric(df[col], errors='coerce')
        
        # Stock inteligente basado en datos existentes
        median_qty = df[col].median()
        if pd.isna(median_qty):
            median_qty = 10.0
        
        problem_mask = (df[col] < 0) | df[col].isna()
        df.loc[problem_mask, col] = median_qty
        
        improvements.append(f"Cantidades optimizadas en '{col}' (mediana: {median_qty:.0f})")
        return improvements
    
    def _optimize_date_column(self, df: pd.DataFrame, col: str) -> List[str]:
        """Optimiza fechas"""
        improvements = []
        
        try:
            df[col] = pd.to_datetime(df[col], errors='coerce')
            # Fechas faltantes = hoy
            df[col] = df[col].fillna(pd.Timestamp.now())
            improvements.append(f"Fechas normalizadas en '{col}'")
        except:
            improvements.append(f"No se pudieron optimizar fechas en '{col}'")
        
        return improvements
    
    def _optimize_boolean_column(self, df: pd.DataFrame, col: str) -> List[str]:
        """Optimiza valores booleanos"""
        improvements = []
        original = df[col].copy()
        
        # Aplicar mapeo universal de booleanos
        df[col] = df[col].astype(str).str.lower().replace(self.universal_corrections['boolean_values'])
        
        changes = (original.astype(str) != df[col].astype(str)).sum()
        if changes > 0:
            improvements.append(f"Valores booleanos estandarizados en '{col}' ({changes} cambios)")
        
        return improvements
    
    def _optimize_category_column(self, df: pd.DataFrame, col: str) -> List[str]:
        """Optimiza categorías"""
        improvements = []
        
        # Normalizar a minúsculas y limpiar
        df[col] = df[col].astype(str).str.lower().str.strip()
        
        improvements.append(f"Categorías normalizadas en '{col}'")
        return improvements
    
    def _optimize_address_column(self, df: pd.DataFrame, col: str) -> List[str]:
        """Optimiza direcciones"""
        improvements = []
        
        # Capitalizar direcciones
        df[col] = df[col].str.title()
        
        improvements.append(f"Direcciones formateadas en '{col}'")
        return improvements
    
    def _universal_null_cleaning(self, df: pd.DataFrame) -> List[str]:
        """Limpieza universal de valores nulos"""
        improvements = []
        
        null_values = ['nan', 'NaN', 'null', 'NULL', '', 'None', 'NONE', 'n/a', 'N/A', 'undefined', '-']
        original_nulls = df.isnull().sum().sum()
        
        df.replace(null_values, pd.NA, inplace=True)
        
        new_nulls = df.isnull().sum().sum()
        if new_nulls > original_nulls:
            improvements.append(f"Identificados {new_nulls - original_nulls} valores nulos adicionales")
        
        return improvements
    
    def _optimize_data_types(self, df: pd.DataFrame) -> List[str]:
        """Optimiza tipos de datos automáticamente"""
        improvements = []
        optimized = 0
        
        for col in df.columns:
            if df[col].dtype == 'object':
                # Intentar convertir a numérico
                numeric = pd.to_numeric(df[col], errors='coerce')
                if not numeric.isna().all():
                    df[col] = numeric
                    optimized += 1
        
        if optimized > 0:
            improvements.append(f"Tipos de datos optimizados en {optimized} columnas")
        
        return improvements
    
    def _generate_missing_data(self, df: pd.DataFrame, column_types: Dict[str, str]) -> List[str]:
        """Genera datos faltantes inteligentemente"""
        improvements = []
        
        for col, col_type in column_types.items():
            missing_count = df[col].isna().sum()
            if missing_count > 0:
                if col_type == 'price':
                    median_val = df[col].median()
                    df[col] = df[col].fillna(median_val if pd.notna(median_val) else 100.0)
                elif col_type == 'quantity':
                    median_val = df[col].median()
                    df[col] = df[col].fillna(median_val if pd.notna(median_val) else 10.0)
                elif col_type == 'boolean':
                    df[col] = df[col].fillna('no')
                elif col_type == 'category':
                    mode_val = df[col].mode()
                    df[col] = df[col].fillna(mode_val[0] if len(mode_val) > 0 else 'general')
                
                if missing_count > 0:
                    improvements.append(f"Generados {missing_count} valores faltantes en '{col}'")
        
        return improvements
    
    def _fix_invalid_emails(self, df: pd.DataFrame, email_col: str):
        """Corrige emails inválidos usando otros datos disponibles"""
        # Buscar columna de nombres
        name_cols = [c for c in df.columns if any(p in c.lower() for p in ['nombre', 'name'])]
        
        if name_cols:
            name_col = name_cols[0]
            invalid_mask = ~df[email_col].str.contains('@.*\\.', na=False)
            
            for idx in df[invalid_mask].index:
                if pd.notna(df.loc[idx, name_col]):
                    clean_name = str(df.loc[idx, name_col]).lower().replace(' ', '.')
                    df.loc[idx, email_col] = f"{clean_name}@gmail.com"