#!/bin/bash
# you need "jq" and "mogrify" installed to run this script

echo Downloading identity thumbnails from NetrunnerDB

# download card list
curl https://netrunnerdb.com/api/2.0/public/cards > cards.json
types=( $(cat cards.json | jq -r '.data[].type_code' ) )
sides=( $(cat cards.json | jq -r '.data[].side_code' ) )
codes=( $(cat cards.json | jq -r '.data[].code' ) )
titles=( $(cat cards.json | jq -r '.data[].title' | tr -s ' ' | tr ' ' '-' | tr '[:upper:]' '[:lower:]' | sed "s/[^a-z0-9.-]//g") )
i=0;

# iterate over cards
for card in "${codes[@]}"
do
    if [ ${types[$i]} == "identity" ]; then
        title=${titles[$i]}
        # download if not already present
        if [ ! -f "public/img/ids/$card.png" ]; then
            echo "Downloading card image: $title"
            curl "https://netrunnerdb.com/card_image/large/$card.jpg" > "public/img/ids/$card.png"
            # crop and resize
            if [ $card -lt 26000 ]; then
                # old FFG ID templates
                if [ ${sides[$i]} == "corp" ]; then
                    echo corp
                    mogrify -crop 215x215+43+68 -resize 80x80 "public/img/ids/$card.png"
                else
                    echo runner
                    mogrify -crop 232x232+33+56 -resize 80x80 "public/img/ids/$card.png"
                fi
            else
                # new NSG ID templates
                if [ ${sides[$i]} == "corp" ]; then
                    echo corp
                    mogrify -crop 237x237+30+46 -resize 80x80 "public/img/ids/$card.png"
                else
                    echo runner
                    mogrify -crop 226x226+39+56 -resize 80x80 "public/img/ids/$card.png"
                fi
            fi
        fi
    fi
    ((i++))
done

# delete missing card images
find public/img/ids/ -name "*.png" -size -9k -delete
