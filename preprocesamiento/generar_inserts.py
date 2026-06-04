# Script que toma el CSV de NBA y genera los INSERTs SQL
# Autor: Luis Gonzalez - COMP4018

import pandas as pd
import os

CSV_INPUT  = 'data/all_seasons.csv'
SQL_OUTPUT = 'sql/inserts.sql'

# Info de equipos para llenar la tabla Equipo (el CSV solo trae abreviaciones)
EQUIPOS_INFO = {
    'ATL': ('Atlanta Hawks',           'Atlanta',        'Este'),
    'BOS': ('Boston Celtics',          'Boston',         'Este'),
    'BKN': ('Brooklyn Nets',           'Brooklyn',       'Este'),
    'BRK': ('Brooklyn Nets',           'Brooklyn',       'Este'),
    'CHA': ('Charlotte Hornets',       'Charlotte',      'Este'),
    'CHH': ('Charlotte Hornets',       'Charlotte',      'Este'),
    'CHI': ('Chicago Bulls',           'Chicago',        'Este'),
    'CLE': ('Cleveland Cavaliers',     'Cleveland',      'Este'),
    'DAL': ('Dallas Mavericks',        'Dallas',         'Oeste'),
    'DEN': ('Denver Nuggets',          'Denver',         'Oeste'),
    'DET': ('Detroit Pistons',         'Detroit',        'Este'),
    'GSW': ('Golden State Warriors',   'San Francisco',  'Oeste'),
    'HOU': ('Houston Rockets',         'Houston',        'Oeste'),
    'IND': ('Indiana Pacers',          'Indianapolis',   'Este'),
    'LAC': ('Los Angeles Clippers',    'Los Angeles',    'Oeste'),
    'LAL': ('Los Angeles Lakers',      'Los Angeles',    'Oeste'),
    'MEM': ('Memphis Grizzlies',       'Memphis',        'Oeste'),
    'MIA': ('Miami Heat',              'Miami',          'Este'),
    'MIL': ('Milwaukee Bucks',         'Milwaukee',      'Este'),
    'MIN': ('Minnesota Timberwolves',  'Minneapolis',    'Oeste'),
    'NJN': ('New Jersey Nets',         'New Jersey',     'Este'),
    'NOH': ('New Orleans Hornets',     'New Orleans',    'Oeste'),
    'NOK': ('New Orleans/OKC Hornets', 'Oklahoma City',  'Oeste'),
    'NOP': ('New Orleans Pelicans',    'New Orleans',    'Oeste'),
    'NYK': ('New York Knicks',         'New York',       'Este'),
    'OKC': ('Oklahoma City Thunder',   'Oklahoma City',  'Oeste'),
    'ORL': ('Orlando Magic',           'Orlando',        'Este'),
    'PHI': ('Philadelphia 76ers',      'Philadelphia',   'Este'),
    'PHX': ('Phoenix Suns',            'Phoenix',        'Oeste'),
    'POR': ('Portland Trail Blazers',  'Portland',       'Oeste'),
    'SAC': ('Sacramento Kings',        'Sacramento',     'Oeste'),
    'SAS': ('San Antonio Spurs',       'San Antonio',    'Oeste'),
    'SEA': ('Seattle SuperSonics',     'Seattle',        'Oeste'),
    'TOR': ('Toronto Raptors',         'Toronto',        'Este'),
    'UTA': ('Utah Jazz',               'Salt Lake City', 'Oeste'),
    'VAN': ('Vancouver Grizzlies',     'Vancouver',      'Oeste'),
    'WAS': ('Washington Wizards',      'Washington',     'Este'),
}

# Pais -> continente
PAIS_CONTINENTE = {
    'USA': 'America', 'Canada': 'America', 'Brazil': 'America',
    'Argentina': 'America', 'Mexico': 'America', 'Dominican Republic': 'America',
    'Venezuela': 'America', 'Puerto Rico': 'America', 'Haiti': 'America',
    'Jamaica': 'America', 'Bahamas': 'America', 'US Virgin Islands': 'America',
    'Saint Lucia': 'America', 'Saint Vincent and the Grenadines': 'America',
    'Trinidad and Tobago': 'America',
    'Spain': 'Europa', 'France': 'Europa', 'Germany': 'Europa',
    'Italy': 'Europa', 'Lithuania': 'Europa', 'Slovenia': 'Europa',
    'Croatia': 'Europa', 'Serbia': 'Europa', 'Russia': 'Europa',
    'Greece': 'Europa', 'Turkey': 'Asia', 'Sweden': 'Europa',
    'Switzerland': 'Europa', 'Ukraine': 'Europa', 'Latvia': 'Europa',
    'Poland': 'Europa', 'Bosnia and Herzegovina': 'Europa',
    'Bosnia & Herzegovina': 'Europa',
    'Montenegro': 'Europa', 'Czech Republic': 'Europa', 'Georgia': 'Europa',
    'Finland': 'Europa', 'Belgium': 'Europa', 'Netherlands': 'Europa',
    'United Kingdom': 'Europa', 'England': 'Europa', 'Scotland': 'Europa',
    'Ireland': 'Europa', 'Austria': 'Europa', 'Hungary': 'Europa',
    'Macedonia': 'Europa', 'North Macedonia': 'Europa', 'Denmark': 'Europa',
    'Israel': 'Asia', 'Iran': 'Asia', 'China': 'Asia',
    'Japan': 'Asia', 'South Korea': 'Asia', 'Philippines': 'Asia',
    'Taiwan': 'Asia', 'Lebanon': 'Asia',
    'Nigeria': 'Africa', 'Senegal': 'Africa', 'Sudan': 'Africa',
    'South Sudan': 'Africa', 'Cameroon': 'Africa',
    'Democratic Republic of the Congo': 'Africa',
    'Republic of the Congo': 'Africa', 'Mali': 'Africa', 'Egypt': 'Africa',
    'Tunisia': 'Africa', 'Gabon': 'Africa', 'Cape Verde': 'Africa',
    'Ghana': 'Africa', 'Tanzania': 'Africa',
    'Australia': 'Oceania', 'New Zealand': 'Oceania',
}


def escapar_sql(valor):
    # Convierte valores de Python a SQL seguros
    if valor is None or (isinstance(valor, float) and pd.isna(valor)):
        return 'NULL'
    if isinstance(valor, str):
        return "'" + valor.replace("'", "''") + "'"
    return str(valor)


def parsear_temporada(etiqueta):
    # '1996-97' -> (1996, 1997), '1999-00' -> (1999, 2000)
    inicio = int(etiqueta[:4])
    fin_corto = int(etiqueta[5:])
    if fin_corto < 50:
        fin = 2000 + fin_corto
    else:
        fin = 1900 + fin_corto
    return inicio, fin


def main():
    print("Iniciando preprocesamiento...")

    # Cargar el CSV
    df = pd.read_csv(CSV_INPUT)
    print(f"Filas iniciales: {len(df):,}")

    if 'Unnamed: 0' in df.columns:
        df = df.drop(columns=['Unnamed: 0'])

    # Quitar duplicados de (jugador, temporada)
    dup = df.duplicated(subset=['player_name', 'season']).sum()
    df = df.drop_duplicates(subset=['player_name', 'season'], keep='first').reset_index(drop=True)
    print(f"Duplicados eliminados: {dup}")
    print(f"Filas finales: {len(df):,}")

    # Limpiar columnas de texto: quitar espacios y convertir vacios en NaN
    for col in ['player_name', 'team_abbreviation', 'college', 'country', 'season']:
        df[col] = df[col].astype(str).str.strip()
        df.loc[df[col] == '', col] = pd.NA
        df.loc[df[col] == 'nan', col] = pd.NA

    # Sacar valores unicos para tablas de referencia
    paises_unicos = sorted(df['country'].dropna().unique())
    pais_a_id = {p: i + 1 for i, p in enumerate(paises_unicos)}

    universidades_unicas = sorted(df['college'].dropna().unique())
    universidad_a_id = {u: i + 1 for i, u in enumerate(universidades_unicas)}

    equipos_unicos = sorted(df['team_abbreviation'].dropna().unique())
    equipo_a_id = {e: i + 1 for i, e in enumerate(equipos_unicos)}

    temporadas_unicas = sorted(df['season'].dropna().unique())
    temporada_a_id = {t: i + 1 for i, t in enumerate(temporadas_unicas)}

    print(f"Paises: {len(paises_unicos)} | Universidades: {len(universidades_unicas)} | "
          f"Equipos: {len(equipos_unicos)} | Temporadas: {len(temporadas_unicas)}")

    # Un jugador por nombre (primera aparicion = datos fijos)
    jugadores_df = df.drop_duplicates(subset=['player_name'], keep='first').reset_index(drop=True)
    jugador_a_id = {nombre: i + 1 for i, nombre in enumerate(jugadores_df['player_name'])}
    print(f"Jugadores unicos: {len(jugadores_df):,}")

   # Separar la jerarquia - drafteado solo si las 3 columnas son numericas
    es_drafteado = (
        (jugadores_df['draft_year'] != 'Undrafted') &
        (jugadores_df['draft_round'] != 'Undrafted') &
        (jugadores_df['draft_number'] != 'Undrafted')
    )
    drafteados = jugadores_df[es_drafteado].copy()
    no_drafteados = jugadores_df[~es_drafteado].copy()
    print(f"Drafteados: {len(drafteados):,} | No drafteados: {len(no_drafteados):,}")

    # Para no drafteados: ano de ingreso = primera temporada en el CSV
    primera_temporada_por_jugador = df.groupby('player_name')['season'].min().to_dict()

    # Generar el archivo SQL
    os.makedirs(os.path.dirname(SQL_OUTPUT), exist_ok=True)

    with open(SQL_OUTPUT, 'w', encoding='utf-8') as f:
        # Encabezado
        f.write("-- Inserts generados por preprocesamiento/generar_inserts.py\n\n")
        f.write("USE nba_db;\n\n")
        f.write("SET FOREIGN_KEY_CHECKS = 0;\n")
        f.write("SET AUTOCOMMIT = 0;\n")
        f.write("START TRANSACTION;\n\n")

        # Pais
        f.write("-- Pais\n")
        for pais in paises_unicos:
            cid = pais_a_id[pais]
            continente = PAIS_CONTINENTE.get(pais, 'Otro')
            f.write(f"INSERT INTO Pais (id_pais, nombre, continente) VALUES "
                    f"({cid}, {escapar_sql(pais)}, {escapar_sql(continente)});\n")
        f.write("\n")

        # Universidad
        f.write("-- Universidad\n")
        for univ in universidades_unicas:
            uid = universidad_a_id[univ]
            f.write(f"INSERT INTO Universidad (id_universidad, nombre, estado) VALUES "
                    f"({uid}, {escapar_sql(univ)}, NULL);\n")
        f.write("\n")

        # Equipo
        f.write("-- Equipo\n")
        for abr in equipos_unicos:
            eid = equipo_a_id[abr]
            info = EQUIPOS_INFO.get(abr, (abr, None, None))
            nombre_completo, ciudad, conferencia = info
            f.write(f"INSERT INTO Equipo (id_equipo, abreviacion, nombre_completo, ciudad, conferencia) VALUES "
                    f"({eid}, {escapar_sql(abr)}, {escapar_sql(nombre_completo)}, "
                    f"{escapar_sql(ciudad)}, {escapar_sql(conferencia)});\n")
        f.write("\n")

        # Temporada
        f.write("-- Temporada\n")
        for t in temporadas_unicas:
            tid = temporada_a_id[t]
            anio_inicio, anio_fin = parsear_temporada(t)
            f.write(f"INSERT INTO Temporada (id_temporada, etiqueta, anio_inicio, anio_fin) VALUES "
                    f"({tid}, {escapar_sql(t)}, {anio_inicio}, {anio_fin});\n")
        f.write("\n")

        # Jugador
        f.write("-- Jugador\n")
        for _, row in jugadores_df.iterrows():
            jid = jugador_a_id[row['player_name']]
            pid = pais_a_id[row['country']]
            college = row['college']
            uid = universidad_a_id[college] if pd.notna(college) else None
            uid_sql = uid if uid is not None else 'NULL'
            f.write(f"INSERT INTO Jugador (id_jugador, nombre, id_pais, id_universidad) VALUES "
                    f"({jid}, {escapar_sql(row['player_name'])}, {pid}, {uid_sql});\n")
        f.write("\n")

        # JugadorDrafteado
        f.write("-- JugadorDrafteado\n")
        for _, row in drafteados.iterrows():
            jid = jugador_a_id[row['player_name']]
            anio = int(row['draft_year'])
            ronda = int(row['draft_round'])
            pick = int(row['draft_number'])
            eid_draft = equipo_a_id[row['team_abbreviation']]
            f.write(f"INSERT INTO JugadorDrafteado (id_jugador, anio_draft, ronda, numero_pick, id_equipo_draft) "
                    f"VALUES ({jid}, {anio}, {ronda}, {pick}, {eid_draft});\n")
        f.write("\n")

        # JugadorNoDrafteado
        f.write("-- JugadorNoDrafteado\n")
        for _, row in no_drafteados.iterrows():
            jid = jugador_a_id[row['player_name']]
            primera = primera_temporada_por_jugador[row['player_name']]
            anio_ingreso, _ = parsear_temporada(primera)
            f.write(f"INSERT INTO JugadorNoDrafteado (id_jugador, anio_ingreso_liga) VALUES "
                    f"({jid}, {anio_ingreso});\n")
        f.write("\n")

        # EstadisticasTemporada
        f.write("-- EstadisticasTemporada\n")
        for _, row in df.iterrows():
            jid = jugador_a_id[row['player_name']]
            tid = temporada_a_id[row['season']]
            eid = equipo_a_id[row['team_abbreviation']]
            f.write(f"INSERT INTO EstadisticasTemporada "
                    f"(id_jugador, id_temporada, id_equipo, edad, altura, peso, gp, pts, reb, ast, net_rating) "
                    f"VALUES ({jid}, {tid}, {eid}, "
                    f"{int(row['age'])}, {row['player_height']}, {row['player_weight']}, "
                    f"{int(row['gp'])}, {row['pts']}, {row['reb']}, {row['ast']}, {row['net_rating']});\n")
        f.write("\n")

        # EstadisticasAvanzadas
        f.write("-- EstadisticasAvanzadas\n")
        for _, row in df.iterrows():
            jid = jugador_a_id[row['player_name']]
            tid = temporada_a_id[row['season']]
            f.write(f"INSERT INTO EstadisticasAvanzadas "
                    f"(id_jugador, id_temporada, oreb_pct, dreb_pct, usg_pct, ts_pct, ast_pct) "
                    f"VALUES ({jid}, {tid}, {row['oreb_pct']}, {row['dreb_pct']}, "
                    f"{row['usg_pct']}, {row['ts_pct']}, {row['ast_pct']});\n")
        f.write("\n")

        f.write("COMMIT;\n")
        f.write("SET FOREIGN_KEY_CHECKS = 1;\n")
        f.write("SET AUTOCOMMIT = 1;\n")

    kb = os.path.getsize(SQL_OUTPUT) / 1024
    print(f"Archivo generado: {SQL_OUTPUT} ({kb:,.1f} KB)")
    print("Listo.")


if __name__ == '__main__':
    main()