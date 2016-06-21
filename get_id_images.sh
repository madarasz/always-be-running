#!/bin/bash
echo Downloading identity thumbnails from NetrunnerDB

curl https://netrunnerdb.com/api/cards/ > cards.json
cards=( $(cat cards.json | jq -r '.[].imagesrc' ) )
types=( $(cat cards.json | jq -r '.[].type_code' ) )
imagesrc=( $(cat cards.json | jq -r '.[].imagesrc' ) )
sides=( $(cat cards.json | jq -r '.[].side_code' ) )
codes=( $(cat cards.json | jq -r '.[].code' ) )
titles=( $(cat cards.json | jq -r '.[].title' | tr -s ' ' | tr ' ' '-' | tr '[:upper:]' '[:lower:]' | sed "s/[^a-z0-9.-]//g") )
i=0;
for card in "${cards[@]}"
do
    if [ ${types[$i]} == "identity" ]; then
        title=${titles[$i]}
        code=${codes[$i]}
        if [ ! -f "public/img/ids/$code.png" ]; then
            image=${imagesrc[$i]}
            echo "Downloading card image: $title"
            curl "https://netrunnerdb.com$image" > "public/img/ids/$code.png"
            if [ ${sides[$i]} == "corp" ]; then
                echo corp
                mogrify -crop 224x224+38+67 -resize 20x20 "public/img/ids/$code.png"
            else
                echo runner
                mogrify -crop 238x238+31+51 -resize 20x20 "public/img/ids/$code.png"
            fi
        fi
    fi
    ((i++))
done

