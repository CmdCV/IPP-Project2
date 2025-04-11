#!/bin/bash

GREEN='\033[0;32m'
RED='\033[0;31m'
RESET='\033[0m'

for i in {1..38}; do
    if [ "$i" -eq 14 ]; then
        OUTPUT=$(echo "ahoj" | php interpret.php --source="./student/inputs/$i.xml" 2>&1)
    else
        OUTPUT=$(php interpret.php --source="./student/inputs/$i.xml" 2>&1)
    fi

    if [ $? -eq 0 ]; then
        echo -e "${i} ${GREEN}SUCCESS${RESET}"
    else
        echo -e "${i} ${RED}FAILED${RESET}"
        echo "$OUTPUT" | sed 's/^/    /'
    fi
done