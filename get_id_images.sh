#!/bin/bash
echo Downloading identity thumbnails from NetrunnerDB

curl https://netrunnerdb.com/api/2.0/public/cards > cards.json
types=( $(cat cards.json | jq -r '.data[].type_code' ) )
sides=( $(cat cards.json | jq -r '.data[].side_code' ) )
codes=( $(cat cards.json | jq -r '.data[].code' ) )
titles=( $(cat cards.json | jq -r '.data[].title' | tr -s ' ' | tr ' ' '-' | tr '[:upper:]' '[:lower:]' | sed "s/[^a-z0-9.-]//g") )
i=0;
for card in "${codes[@]}"
do
    if [ ${types[$i]} == "identity" ]; then
        title=${titles[$i]}
        if [ ! -f "public/img/ids/$card.png" ]; then
            image=${cards[$i]}
            echo "Downloading card image: $title"
            curl "https://netrunnerdb.com/card_image/$card.png" > "public/img/ids/$card.png"
            if [ ${sides[$i]} == "corp" ]; then
                echo corp
                mogrify -crop 224x224+38+67 -resize 20x20 "public/img/ids/$card.png"
            else
                echo runner
                mogrify -crop 238x238+31+51 -resize 20x20 "public/img/ids/$card.png"
            fi
        fi
    fi
    ((i++))
done

