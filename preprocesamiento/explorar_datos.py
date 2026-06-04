import pandas as pd

# Cargar el CSV
print("=" * 70)
print("EXPLORACIÓN DEL DATASET: all_seasons.csv")
print("=" * 70)

df = pd.read_csv('data/all_seasons.csv')

# 1. Información general
print(f"\n[1] DIMENSIONES DEL DATASET")
print(f"    Filas totales:    {len(df):,}")
print(f"    Columnas totales: {len(df.columns)}")

# 2. Valores únicos por columna clave (para dimensionar las tablas)
print(f"\n[2] VALORES ÚNICOS POR ATRIBUTO")
print(f"    Jugadores únicos:    {df['player_name'].nunique():,}")
print(f"    Equipos únicos:      {df['team_abbreviation'].nunique()}")
print(f"    Temporadas únicas:   {df['season'].nunique()}")
print(f"    Países únicos:       {df['country'].nunique()}")
print(f"    Universidades únicas:{df['college'].nunique()}")

# 3. Análisis de la columna que justifica la herencia (draft_year)
print(f"\n[3] ANÁLISIS DE draft_year (JUSTIFICA LA HERENCIA)")
total = len(df)
undrafted = (df['draft_year'] == 'Undrafted').sum()
drafted = total - undrafted
print(f"    Filas con 'Undrafted':  {undrafted:,} ({undrafted/total*100:.1f}%)")
print(f"    Filas con año de draft: {drafted:,} ({drafted/total*100:.1f}%)")

# Confirmar a nivel de JUGADOR, no de fila
jugadores_undrafted = df[df['draft_year'] == 'Undrafted']['player_name'].nunique()
jugadores_drafted   = df[df['draft_year'] != 'Undrafted']['player_name'].nunique()
print(f"    Jugadores no drafteados:  {jugadores_undrafted}")
print(f"    Jugadores drafteados:     {jugadores_drafted}")

# 4. Valores nulos por columna
print(f"\n[4] VALORES NULOS POR COLUMNA")
nulos = df.isnull().sum()
for col, n in nulos.items():
    if n > 0:
        print(f"    {col}: {n:,} nulos")
if nulos.sum() == 0:
    print("    No hay valores nulos explícitos (NaN)")

# 5. ¿Hay strings que actúan como nulos? Por ejemplo en college
print(f"\n[5] VALORES ESPECIALES EN 'college' (universidades nulas)")
print(f"    Valores únicos en college (primeros 10):")
for val in df['college'].dropna().unique()[:10]:
    print(f"      - '{val}'")

# 6. ¿Un jugador puede aparecer en múltiples equipos en la misma temporada?
print(f"\n[6] ¿JUGADOR–TEMPORADA ES ÚNICO?")
duplicados = df.duplicated(subset=['player_name', 'season']).sum()
print(f"    Filas con (jugador, temporada) duplicado: {duplicados}")
if duplicados > 0:
    print(f"    ⚠️ ATENCIÓN: hay jugadores con múltiples filas en la misma temporada.")
    print(f"       (probablemente cambiaron de equipo a mitad de temporada)")
    ejemplos = df[df.duplicated(subset=['player_name', 'season'], keep=False)].sort_values(['player_name', 'season']).head(6)
    print(f"\n    Ejemplos:")
    print(ejemplos[['player_name', 'season', 'team_abbreviation']].to_string(index=False))

# 7. Rango temporal
print(f"\n[7] RANGO TEMPORAL")
print(f"    Primera temporada: {df['season'].min()}")
print(f"    Última temporada:  {df['season'].max()}")

# 8. Listar todas las temporadas
print(f"\n[8] TEMPORADAS PRESENTES ({df['season'].nunique()} en total)")
print(f"    {sorted(df['season'].unique())}")

print("\n" + "=" * 70)
print("EXPLORACIÓN COMPLETADA")
print("=" * 70)