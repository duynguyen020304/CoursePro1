#!/usr/bin/env bash
TARGET_PYTHON="3.11.13"

get_rc_file() {
  if [ -n "${ZSH_VERSION:-}" ]; then
    [ -f "$HOME/.zshrc" ] && echo "$HOME/.zshrc" || echo "$HOME/.bashrc"
  else
    if [ -f "$HOME/.bashrc" ]; then
      echo "$HOME/.bashrc"
    elif [ -f "$HOME/.bash_profile" ]; then
      echo "$HOME/.bash_profile"
    elif [ -f "$HOME/.profile" ]; then
      echo "$HOME/.profile"
    else
      echo "$HOME/.bashrc"
    fi
  fi
}
RC_FILE="$(get_rc_file)"

ensure_line() {
  local LINE="$1"
  local FILE="$2"
  if [ ! -f "$FILE" ]; then
    touch "$FILE"
  fi
  if ! grep -Fqs "$LINE" "$FILE"; then
    echo "$LINE" >> "$FILE"
  fi
}


install_pyenv() {
  if [ -d "$HOME/.pyenv" ]; then
    echo "pyenv seems to be already installed at $HOME/.pyenv"
    return 0
  fi
  echo "Downloading and running pyenv installer..."
  curl -fsSL https://pyenv.run | bash
}

configure_shell() {
  local LINE_PYENV_ROOT='export PYENV_ROOT="$HOME/.pyenv"'
  local LINE_PATH='export PATH="$PYENV_ROOT/bin:$PATH"'
  local LINE_INIT='eval "$(pyenv init - bash)"'

  ensure_line "$LINE_PYENV_ROOT" "$RC_FILE"
  ensure_line "$LINE_PATH" "$RC_FILE"
  ensure_line "$LINE_INIT" "$RC_FILE"

  if [ -f "$HOME/.bash_profile" ]; then
    ensure_line "$LINE_PYENV_ROOT" "$HOME/.bash_profile"
    ensure_line "$LINE_PATH" "$HOME/.bash_profile"
    ensure_line "$LINE_INIT" "$HOME/.bash_profile"
  elif [ -f "$HOME/.profile" ]; then
    ensure_line "$LINE_PYENV_ROOT" "$HOME/.profile"
    ensure_line "$LINE_PATH" "$HOME/.profile"
    ensure_line "$LINE_INIT" "$HOME/.profile"
  fi

  export PYENV_ROOT="$HOME/.pyenv"
  export PATH="$PYENV_ROOT/bin:$PATH"
  if command -v pyenv >/dev/null 2>&1; then
    eval "$(pyenv init - bash)"
  fi
}

install_python_version() {
  if ! command -v pyenv >/dev/null 2>&1; then
    echo "pyenv is not in PATH. Attempting to reload..."
    export PYENV_ROOT="$HOME/.pyenv"
    export PATH="$PYENV_ROOT/bin:$PATH"
    if command -v pyenv >/dev/null 2>&1; then
      eval "$(pyenv init - bash)"
    else
      echo "pyenv could not be found. Please reopen the terminal or re-run the script." >&2
      exit 1
    fi
  fi

  if pyenv versions --bare | grep -Fxq "$TARGET_PYTHON"; then
    echo "Version $TARGET_PYTHON is already installed."
  else
    echo "Installing Python $TARGET_PYTHON using pyenv..."
    pyenv install --skip-existing "$TARGET_PYTHON"
  fi

  echo "Setting Python $TARGET_PYTHON as the default (global)."
  pyenv global "$TARGET_PYTHON"

  echo "Checking current Python version..."
  pyenv versions
  python --version 2>&1 || true
  pyenv which python 2>&1 || true
}

# Install pip packages into the pyenv-managed TARGET_PYTHON.
# Behavior:
#  - If requirements.txt exists in cwd -> pip install -r requirements.txt
#  - Else if packages.txt exists -> read lines and install (ignore blank lines and lines starting with #)
#  - Else if interactive -> prompt user to type space-separated package names
#  - Else -> skip
install_pip_packages() {
  # Ensure pyenv is available and python binary for TARGET_PYTHON exists
  if ! command -v pyenv >/dev/null 2>&1; then
    export PYENV_ROOT="$HOME/.pyenv"
    export PATH="$PYENV_ROOT/bin:$PATH"
    if command -v pyenv >/dev/null 2>&1; then
      eval "$(pyenv init - bash)"
    else
      echo "pyenv is not available; cannot install pip packages." >&2
      return 2
    fi
  fi

  if ! pyenv versions --bare | grep -Fxq "$TARGET_PYTHON"; then
    echo "Target Python $TARGET_PYTHON is not installed. Installing it first..."
    pyenv install "$TARGET_PYTHON"
    pyenv global "$TARGET_PYTHON"
  fi

  # Determine python binary for the requested version
  PY_BIN="$(pyenv root)/versions/$TARGET_PYTHON/bin/python"
  if [ ! -x "$PY_BIN" ]; then
    PY_BIN="$(pyenv which python 2>/dev/null || true)"
  fi
  if [ -z "$PY_BIN" ] || [ ! -x "$PY_BIN" ]; then
    echo "Could not find python binary for version $TARGET_PYTHON." >&2
    return 3
  fi

  echo "Using python: $PY_BIN"
  echo "Upgrading pip, setuptools and wheel..."
  "$PY_BIN" -m pip install --upgrade pip setuptools wheel || {
    echo "Failed to upgrade pip/setuptools/wheel" >&2
    return 4
  }

  if [ -f requirements.txt ]; then
      echo "Found requirements.txt in current directory. Installing..."
      "$PY_BIN" -m pip install -r "./requirements.txt" || {
        echo "pip install -r requirements.txt failed" >&2
        return 5
      }
      echo "Installed packages from requirements.txt"
      pyenv rehash || true
      return 0
  fi

  # If packages.txt exists (one package per line), parse and install
  if [ -f "./packages.txt" ]; then
    echo "Found packages.txt in current directory. Reading packages..."
    mapfile -t pkgs < <(sed -E 's/^[[:space:]]+|[[:space:]]+$//g' packages.txt | sed '/^\s*#/d' | sed '/^\s*$/d')
    if [ "${#pkgs[@]}" -gt 0 ]; then
      echo "Installing: ${pkgs[*]}"
      "$PY_BIN" -m pip install --upgrade "${pkgs[@]}" || {
        echo "pip install from packages.txt failed" >&2
        return 6
      }
      pyenv rehash || true
      return 0
    else
      echo "packages.txt contained no packages (after removing comments/blank lines)."
    fi
  fi

  # If interactive, prompt user
  if [ -t 0 ]; then
    echo -n "Enter pip packages to install (space-separated), or press Enter to skip: "
    IFS= read -r pkgline
    if [ -n "$pkgline" ]; then
      # Split into array respecting simple quoting/word splitting
      read -r -a pkgs <<< "$pkgline"
      if [ "${#pkgs[@]}" -gt 0 ]; then
        echo "Installing: ${pkgs[*]}"
        "$PY_BIN" -m pip install --upgrade "${pkgs[@]}" || {
          echo "pip install failed" >&2
          return 7
        }
        pyenv rehash || true
        return 0
      fi
    else
      echo "No packages entered; skipping pip install."
      return 0
    fi
  else
    echo "No requirements.txt or packages.txt found and not running interactively; skipping pip install."
    return 0
  fi
}

echo "Starting automatic pyenv installation on Ubuntu with default Python: ${TARGET_PYTHON}"
install_pyenv
configure_shell

if [ -f "$RC_FILE" ]; then
  set +u
  . "$RC_FILE" 2>/dev/null || true
  set -u
fi

install_python_version

# Automatically attempt to install pip packages (requirements.txt, packages.txt, or interactive prompt)
install_pip_packages || true

echo "Done. You can open a new terminal or run: exec \"$SHELL\" to reload the configuration."
echo "Quick check: pyenv versions; pyenv global; python --version"
