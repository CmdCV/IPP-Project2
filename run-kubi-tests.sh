#!/bin/bash

# Nastavení
TESTS_DIR="tests"
VENV_DIR=".venv"

# Stažení testů, pokud neexistují
if [ ! -d "$TESTS_DIR" ]; then
  echo "Stahuji testy..."
  git git clone git@github.com:Kubikuli/IPP_proj2-tests.git "$TESTS_DIR"
else
  echo "Testy již existují, kontroluji aktualizace..."
  cd "$TESTS_DIR" || exit
  git fetch
  LOCAL=$(git rev-parse @)
  REMOTE=$(git rev-parse @{u})
  if [ "$LOCAL" != "$REMOTE" ]; then
    echo "Aktualizuji testy..."
    git pull
  else
    echo "Testy jsou aktuální."
  fi
  cd ..
fi

# Nastavení virtuálního prostředí
if [ ! -d "$VENV_DIR" ]; then
  echo "Vytvářím virtuální prostředí..."
  python3 -m venv "$VENV_DIR"
fi

# Aktivace virtuálního prostředí
echo "Aktivuji virtuální prostředí..."
source "$VENV_DIR/bin/activate"

# Instalace požadovaných balíčků, pokud nejsou nainstalovány
REQUIREMENTS_HASH=$(md5sum "$TESTS_DIR/requirements.txt" | awk '{ print $1 }')
INSTALLED_HASH_FILE="$VENV_DIR/requirements.hash"

if [ ! -f "$INSTALLED_HASH_FILE" ] || [ "$(cat "$INSTALLED_HASH_FILE")" != "$REQUIREMENTS_HASH" ]; then
  echo "Instaluji požadované balíčky..."
  pip install -r "$TESTS_DIR/requirements.txt"
  echo "$REQUIREMENTS_HASH" > "$INSTALLED_HASH_FILE"
else
  echo "Požadované balíčky jsou již nainstalovány."
fi

# Spuštění testů
echo "Spouštím testy..."
pytest

# Deaktivace virtuálního prostředí
deactivate
