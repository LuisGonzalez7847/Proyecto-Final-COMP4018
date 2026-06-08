# Script de exploracion del CSV antes de hacer el preprocesamiento

import pandas as pd

print("Explorando dataset all_seasons.csv")
print("=" * 50)

df = pd.read_csv('data/all_seasons.csv')

# Info general
print(f"\nFilas totales: {len(df):,}")
print(f"Columnas totales: {len(df.columns)}")

# Valores unicos por columna importante
print(f"\nValores unicos:")
print(f"  Jugadores:    {df['player_name'].nunique():,}")
print(f"  Equipos:      {df['team_abbreviation'].nunique()}")
print(f"  Temporadas:   {df['season'].nunique()}")
print(f"  Paises:       {df['country'].nunique()}")
print(f"  Universidades:{df['college'].nunique()}")

# Analizar draft_year para validar la herencia
print(f"\nAnalisis de draft_year:")
total = len(df)
undrafted = (df['draft_year'] == 'Undrafted').sum()
drafted = total - undrafted
print(f"  Filas 'Undrafted':  {undrafted:,} ({undrafted/total*100:.1f}%)")
print(f"  Filas con ano:      {drafted:,} ({drafted/total*100:.1f}%)")

jugadores_undrafted = df[df['draft_year'] == 'Undrafted']['player_name'].nunique()
jugadores_drafted   = df[df['draft_year'] != 'Undrafted']['player_name'].nunique()
print(f"  Jugadores no drafteados: {jugadores_undrafted}")
print(f"  Jugadores drafteados:    {jugadores_drafted}")

# Valores nulos
print(f"\nValores nulos por columna:")
nulos = df.isnull().sum()
for col, n in nulos.items():
    if n > 0:
        print(f"  {col}: {n:,}")
if nulos.sum() == 0:
    print("  No hay nulos")

# Ver si hay (jugador, temporada) duplicado - cambios de equipo a mitad de temporada
print(f"\nDuplicados de (jugador, temporada):")
duplicados = df.duplicated(subset=['player_name', 'season']).sum()
print(f"  Filas duplicadas: {duplicados}")
if duplicados > 0:
    ejemplos = df[df.duplicated(subset=['player_name', 'season'], keep=False)].sort_values(['player_name', 'season']).head(6)
    print(ejemplos[['player_name', 'season', 'team_abbreviation']].to_string(index=False))

# Rango temporal
print(f"\nRango: {df['season'].min()} a {df['season'].max()}")
print(f"Total temporadas: {df['season'].nunique()}")