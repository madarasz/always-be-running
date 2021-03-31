#!/bin/bash
# you need "jq" and "mogrify" installed to run this script

echo Downloading identity thumbnails from NetrunnerDB

# download card list
curl https://netrunnerdb.com/api/2.0/public/cards > cards.json
types=( $(cat cards.json | jq -r '.data[].type_code' ) )
sides=( $(cat cards.json | jq -r '.data[].side_code' ) )
codes=( $(cat cards.json | jq -r '.data[].code' ) )
imageurls=( $(cat cards.json | jq -r '.data[].image_url' ) )
titles=( $(cat cards.json | jq -r '.data[].title' | tr -s ' ' | tr ' ' '-' | tr '[:upper:]' '[:lower:]' | sed "s/[^a-z0-9.-]//g") )
i=0;

# iterate over cards
for card in "${codes[@]}"
do
    if [ ${types[$i]} == "identity" ]; then
        title=${titles[$i]}
        # download if not already present
        if [ ! -f "public/img/ids/$card.png" ]; then
            imageurl=${imageurls[$i]}
            echo "Downloading card image: $title - $imageurl"

            # download
            if [ "$imageurl" == "null" ]; then
                # download from NetrunnerDB
                if [ $card -lt 31001 ]; then
                  curl "https://netrunnerdb.com/card_image/$card.png" > "public/img/ids/$card.png"
                else
                  curl "https://netrunnerdb.com/card_image/large/$card.jpg" > "public/img/ids/$card.png"
                  mogrify -resize 372x520 "public/img/ids/$card.png"
                fi
            else
                # download from CardGameDB
                curl $imageurl > "public/img/ids/$card.png"
            fi
            # crop and resize
            if [ $card -lt 26000 ]; then
                # old images
                if [ ${sides[$i]} == "corp" ]; then
                    echo corp
                    mogrify -crop 224x224+38+67 -resize 80x80 "public/img/ids/$card.png"
                else
                    echo runner
                    mogrify -crop 238x238+31+51 -resize 80x80 "public/img/ids/$card.png"
                fi
            elif [ $card -lt 30001 ]; then
                # old NISEI images
                if [ ${sides[$i]} == "corp" ]; then
                    echo corp
                    mogrify -crop 355x355+48+75 -resize 80x80 "public/img/ids/$card.png"
                else
                    echo runner
                    mogrify -crop 330x330+62+88 -resize 80x80 "public/img/ids/$card.png"
                fi
            else
                # new NISEI images
                if [ ${sides[$i]} == "corp" ]; then
                    echo corp
                    mogrify -crop 266x266+53+57 -resize 80x80 "public/img/ids/$card.png"
                else
                    echo runner
                    mogrify -crop 266x266+53+61 -resize 80x80 "public/img/ids/$card.png"
                fi
            fi
        fi
    fi
    ((i++))
done

# delete missing card images
find public/img/ids/ -name "*.png" -size -9k -delete
