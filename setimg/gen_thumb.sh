#!/bin/bash

for file in `ls png2880/$1*.png`; do
  LEGOID=`echo $file | sed 's/png2880\///g' | sed 's/_cover.png//g'`;
  FILE1600="croppng1600/${LEGOID}_cover.png";
  echo $FILE1600;
  curl -o $FILE1600 "http://localhost/pic.php?id=${LEGOID}&size=1600&nomark=1&output=png"
  FILE150="thumb150/${LEGOID}_150.jpg";
  echo $FILE150;
  curl -o $FILE150 "http://localhost/pic.php?id=${LEGOID}&size=150&nomark=1"
  FILETB="tb_main/${LEGOID}_800.jpg";
  echo $FILETB;
  curl -o $FILETB "http://localhost/pic.php?id=${LEGOID}&square"
  x=99;
    
  while [ $(stat -f '%z' $FILETB) -ge 512000 ]
  do
     curl -o $FILETB "http://localhost/pic.php?id=${LEGOID}&quality=${x}&square"
     let "x -= 1";
  done
done
