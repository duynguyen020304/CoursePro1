#!/usr/bin/env bash
set -euo pipefail
TARGET_PYTHON="${1:-3.11.6}"
detect_ubuntu() {
  if [ -f /etc/os-release ]; then
    . /etc/os-release
    if [ "${ID:-}" != "ubuntu" ]; then
      echo "Chỉ hỗ trợ Ubuntu. Phát hiện: ${PRETTY_NAME:-$ID}" >&2
      exit 1
    fi
  else
    echo "Không nhận diện được Ubuntu (không có /etc/os-release)." >&2
    exit 1
  fi
}
detect_ubuntu
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
install_dependencies_ubuntu() {
  echo "Cài đặt phụ thuộc hệ thống (Ubuntu)..."
  sudo apt-get update -y
  sudo DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    build-essential curl git ca-certificates \
    libssl-dev zlib1g-dev libbz2-dev libreadline-dev libsqlite3-dev \
    libncurses5-dev libncursesw5-dev libffi-dev liblzma-dev \
    xz-utils tk-dev
  sudo update-ca-certificates || true
  echo "Phụ thuộc đã được cài đặt (nếu cần)."
}
install_pyenv() {
  if [ -d "$HOME/.pyenv" ]; then
    echo "pyenv có vẻ đã được cài đặt tại $HOME/.pyenv"
    return 0
  fi
  echo "Tải và chạy pyenv installer..."
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
    echo "pyenv chưa có trong PATH. Đang cố gắng nạp lại..."
    export PYENV_ROOT="$HOME/.pyenv"
    export PATH="$PYENV_ROOT/bin:$PATH"
    if command -v pyenv >/dev/null 2>&1; then
      eval "$(pyenv init - bash)"
    else
      echo "Không thể tìm thấy pyenv. Vui lòng mở lại terminal hoặc chạy lại script." >&2
      exit 1
    fi
  fi
  if pyenv versions --bare | grep -Fxq "$TARGET_PYTHON"; then
    echo "Phiên bản $TARGET_PYTHON đã được cài đặt trước đó."
  else
    echo "Cài đặt Python $TARGET_PYTHON bằng pyenv..."
    pyenv install "$TARGET_PYTHON"
  fi
  echo "Đặt Python $TARGET_PYTHON làm mặc định (global)."
  pyenv global "$TARGET_PYTHON"
  echo "Kiểm tra phiên bản Python hiện tại..."
  pyenv versions
  python --version 2>&1 || true
  pyenv which python 2>&1 || true
}
main() {
  echo "Bắt đầu cài đặt pyenv tự động trên Ubuntu với Python mặc định: ${TARGET_PYTHON}"
  install_dependencies_ubuntu
  install_pyenv
  configure_shell
  if [ -f "$RC_FILE" ]; then
    set +u
    . "$RC_FILE" 2>/dev/null || true
    set -u
  fi
  install_python_version
  echo "Hoàn thành. Bạn có thể mở terminal mới hoặc chạy: exec \"$SHELL\" để tải lại cấu hình."
  echo "Kiểm tra nhanh: pyenv versions; pyenv global; python --version"
}
main