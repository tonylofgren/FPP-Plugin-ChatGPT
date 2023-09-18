#!/bin/bash
echo "Installing ChatGPT Plugin for FPP..."
pushd $(dirname $(which $0))
target_PWD=$(readlink -f .)
/opt/fpp/scripts/update_plugin ${target_PWD##*/}
echo "Installing default config if not found..."
/bin/mv -n ../plugin.FPP-Plugin-ChatGPT /home/fpp/media/config/ || echo "Could not move plugin.FPP-Plugin-ChatGPT"
echo "Adding RUN-CHATGPT-SCRIPT.sh to script folder."
/bin/mv RUN-CHATGPT-SCRIPT.sh /home/fpp/media/scripts/ || echo "Could not move RUN-CHATGPT-SCRIPT.sh"
. /opt/fpp/scripts/common
setSetting restartFlag 1
popd