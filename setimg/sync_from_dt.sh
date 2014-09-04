#!/bin/bash

#sync from dt
rsync -avz -e 'ssh -i /Users/liuy/.ssh/rsync_key' liuy@fruitpinkie.peking.corp.yahoo.com:~/legopic/ /Library/WebServer/Documents/setimg/original/

#cp _cover.png to png2880
for file in `ls original/*_alt1.png`; do
  FILENAME=`echo $file | sed 's/original\//png2880\//g' | sed 's/alt1/cover/g'`;
  if [[ ! -f $FILENAME ]];
  then
    cp -v $file $FILENAME;
  fi
done

for file in `ls png2880/*.png`; do
  LEGOID=`echo $file | sed 's/png2880\///g' | sed 's/_cover.png//g'`;
  FILE1600="croppng1600/${LEGOID}_cover.png";
  if [[ ! -f $FILE1600 ]];
  then
    echo $FILE1600;
    curl -o $FILE1600 "http://localhost/pic.php?id=${LEGOID}&size=1600&nomark=1&output=png"
  fi
  FILE150="thumb150/${LEGOID}_150.jpg";
  if [[ ! -f $FILE150 ]];
  then
    echo $FILE150;
    curl -o $FILE150 "http://localhost/pic.php?id=${LEGOID}&size=150&nomark=1"
  fi
  FILETB="tb_main/${LEGOID}_800.jpg";
  if [[ ! -f $FILETB ]];
  then
    echo $FILETB;
    curl -o $FILETB "http://localhost/pic.php?id=${LEGOID}&square"
    x=99;
    
    while [ $(stat -f '%z' $FILETB) -ge 512000 ]
    do
       curl -o $FILETB "http://localhost/pic.php?id=${LEGOID}&quality=${x}&square"
       let "x -= 1";
    done
  fi
done
