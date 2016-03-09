#!/usr/bin/env bash

# pouziti:   is_it_ok.sh xlogin01-XYZ.zip testdir
#  
#   - POZOR: obsah adresare zadaneho druhym parametrem bude po dotazu VYMAZAN!
#   - rozbali archiv studenta xlogin01-XYZ.zip do adresare testdir a overi formalni pozadavky pro odevzdani projektu IPP
#   - nasledne vyzkousi spusteni
#   - detaily prubehu jsou logovany do souboru is_it_ok.log v adresari testdir

# Autor: Zbynek Krivka
# Verze: 1.3.4 (2016-03-08)
#  2012-04-03  Zverejnena prvni verze
#  2012-04-09  Pridana kontrola tretiho radku (prispel Vilem Jenis) a maximalni velikosti archivu
#  2012-04-26  Oprava povolenych pripon archivu, aby to odpovidalo pozadavkum v terminu ve WIS
#  2014-02-14  Pridana moznost koncovky tbz u archivu; Podpora koncovky .php; Kontrola zkratek rozsireni
#  2014-02-25  Pridani parametru -d open_basedir="" interpretu php
#  2015-03-12  Pridana chybejici zavorka v popisu chyby tretiho paramatru
#  2015-03-22  Pridana kontrola tretiho radku i v dalsich skriptech (prispela Michaela Lukasova), kontrola neexistence __MACOSX
#  2016-03-08  Pridana kontrola ulohy CLS a odebrana uloha CST (upozornil Michal Ormoš)

LOG="is_it_ok.log"
MAX_ARCHIVE_SIZE=1100000

# Test validity of argument number
if [[ $# -ne 2 ]]; then
  echo "ERROR: Missing arguments or too much arguments!"
  echo "Usage: $0  ARCHIVE  TESTDIR"
  echo "       This script checks formal requirements for archive with solution of IFJ project."
  echo "         ARCHIVE - the filename of archive to check"
  echo "         TESTDIR - temporary directory that can be deleted/removed during testing!"
  exit 2
fi

# extrakce archivu
function unpack_archive () {
  local ext=`echo $1 | cut -d . -f 2,3`
  echo -n "Archive extraction: "
  RETCODE=100  
	if [[ "$ext" = "zip" ]]; then
		unzip -o $1 >> $LOG 2>&1
    RETCODE=$?
	elif [[ "$ext" = "tgz" ]]; then
		tar xfz $1 >> $LOG 2>&1
    RETCODE=$? 
	elif [[ "$ext" = "tbz" || "$ext" = "tbz2" ]]; then
		tar xfj $1 >> $LOG 2>&1
    RETCODE=$? 
	fi
  if [[ $RETCODE -eq 0 ]]; then
    echo "OK"
  elif [[ $RETCODE -eq 100 ]]; then
    echo "ERROR (unsupported extension)"
    exit 1
  else
    echo "ERROR (code $RETCODE)"
    exit 1
  fi
} 

# prevod jmen souboru obsahujicich nepovolene znaky
#function to_small () {
#	local N=`echo $1 | tr "[:upper:]" "[:lower:]"`
#	if [ "$N" != "$1" ]; then
#	    mv "$1" "$N" 2>/dev/null
#      echo "ERROR ($1 -> $N)"
#      exit 1       
#	fi
#} 

# flattening aktualniho adresare + to_small
#function flattening () {
#        local FILE=""
#        local NFILE=""
#	local FILES=`find . -name '*' -type f`
#	for FILE in $FILES; do
#            NFILE=./${FILE##*/}            
#            if [ "$FILE" != "$NFILE" ]; then
#              mv "$FILE" ${NFILE} 2>/dev/null
#              echo "ERROR ($FILE -> $NFILE)"
#              exit 1              
#            fi
#            F=`basename $FILE`
#            if [ "$F" != "Makefile" ]; then
#              to_small ${NFILE}
#            fi
#	done     
#  echo "OK"
#}

# stare odstraneni DOSovskych radku (nyni mozno pouzit i utilitu dos2unix)
#function remove_CR () {
#	FILES=`ls $* 2>/dev/null`
#	for FILE in $FILES; do
#		mv -f "$FILE" "$FILE.tmp"
#		tr -d "\015" < "$FILE.tmp" > "$FILE"
#		rm -f "$FILE.tmp"
#	done
#}

#   Priprava testdir
if [[ -d $2 ]]; then
  #read -p "Do you want to delete $2 directory? (y/n)" RESP
  if [[ 1 ]]; then
    rm -rf $2 2>/dev/null
  else
    echo "ERROR: User cancelled rewriting of existing directory."
    exit 1
  fi
fi
mkdir $2 2>/dev/null
cp $1 $2 2>/dev/null


#   Overeni serveru (ala Eva neni Merlin)
echo -n "Testing on Merlin: "
HN=`hostname`
if [[ $HN = "merlin.fit.vutbr.cz" ]]; then
  echo "Yes"
else
  echo "No"
fi

#   Kontrola jmena archivu
cd $2
touch $LOG
ARCHIVE=`basename $1`
NAME=`echo $ARCHIVE | cut -d . -f 1 | egrep "^x[a-z]{5}[0-9][0-9a-z]-(CHA|CLS|CSV|DKA|JMP|JSN|MKA|SYN|XQR|XTD)$"`
TASK=`echo $ARCHIVE | cut -d . -f 1 | sed 's/^x[a-z]\{5\}[0-9][0-9a-z]-\(CHA\|CLS\|CSV\|DKA\|JMP\|JSN\|MKA\|SYN\|XTD\|XQR\)$/\1/'`
echo -n "Archive name ($ARCHIVE): "
if [[ -n $NAME ]]; then
  echo "OK"
else
  echo "ERROR (the name $NAME does not correspond to a login + task identifier in uppercase)"
fi

#   Kontrola velikosti archivu
echo -n "Checking size of $ARCHIVE: "
ARCHIVE_SIZE=`du --bytes $ARCHIVE | cut -f 1`
if [[ ${ARCHIVE_SIZE} -ge ${MAX_ARCHIVE_SIZE} ]]; then 
  echo "Too big (${ARCHIVE_SIZE} bytes > ${MAX_ARCHIVE_SIZE} bytes)"; 
else 
  echo "OK"; 
fi

#   Extrakce ID ulohy ($TASK)
echo -n "Recognizing task ($TASK): "
TASKLEN=`echo -n $TASK | wc -m`
if [[ $TASKLEN = "3" ]]; then
  echo "OK"
else
  echo "ERROR ($TASK [$TASKLEN] not recognized)"
  exit 1
fi

#   Extrahovat do testdir
unpack_archive ${ARCHIVE}


#   Dokumentace
echo -n "Searching for $TASK-doc.pdf: "
if [[ -f "$TASK-doc.pdf" ]]; then
  echo "OK"
else
  echo "ERROR (not found)"
fi  

echo -n "Project execution test (--help): "
#   Spusteni skriptu 
SCRIPT=`echo $TASK | tr [:upper:] [:lower:]`
if [[ -f $SCRIPT.php ]]; then
   EXT="php" ## Pridano pro pozdeji
   php -d open_basedir="" $SCRIPT.$EXT --help >> $LOG 2>&1
   RETCODE=$?
   if [[ $RETCODE -eq 0 ]]; then
     echo "OK"
   else
     echo "ERROR (returns code $RETCODE)"
     exit 1
   fi
elif [[ -f $SCRIPT.py ]]; then
   EXT="py" ## Pridano pro pozdeji
   python3 $SCRIPT.$EXT --help >> $LOG 2>&1
   RETCODE=$?
   if [[ $RETCODE -eq 0 ]]; then
     echo "OK"
   else
     echo "ERROR (returns code $RETCODE)"
     exit 1
   fi
else
  echo "ERROR ($SCRIPT.php|py not found)"
fi

#   Kontrola rozsireni
echo -n "Presence of file rozsireni (optional): "
if [[ -f rozsireni ]]; then
  echo "Yes"
  echo -n "Unix end of lines in rozsireni: "
  dos2unix -n rozsireni rozsireni.lf >> $LOG 2>&1
  diff rozsireni rozsireni.lf >> $LOG 2>&1
  RETCODE=$?
  if [[ $RETCODE = "0" ]]; then
    UNKNOWN=`cat rozsireni | grep -v -E -e "^(CFL|COM|PAD|VLC|WCH|RUL|STR|WSA|FUN|PAR|JPD|FLA|JER|WHT|RLO|MST|MWS|HTM|NQS|ORD|LOG|VAL)$" | wc -l`
    if [[ $UNKNOWN = "0" ]]; then
      echo "OK"
    else
      echo "ERROR (Unknown bonus identifier or redundant empty line)"
    fi
  else
    echo "ERROR (CRLFs)"
  fi
else
  echo "No"
fi 

#   Kontrola tretiho radku
if [ -n $EXT ]; then
	for file in $(find -regex ".*\.$EXT$"); do
		filename=`basename $file`
		echo -n "Checking third line of the script ($filename): "
		if [ -s $file ]; then
			dos2unix $file >> $LOG 2>&1 
			LOGIN=`echo "$NAME" | sed 's/^\(x[a-z]\{5\}[0-9][0-9a-z]\).*$/\1/'`
			THIRDLINE=`( read; read; read LINE; echo $LINE;) < $file`
			RETCODE=`echo $THIRDLINE | sed -r "s/^#$TASK:$LOGIN$/OK/"`
			if [ "$RETCODE" == "OK" ]; then
				echo "OK"
			else
				echo "ERROR (\"$THIRDLINE\" should be \"#$TASK:$LOGIN\")"
			fi
		else
			echo "OK (empty file)"
		fi
	done
fi

#   Kontrola adresare __MACOSX
if [[ -d __MACOSX ]]; then
  echo "Archive ($ARCHIVE) should not contain __MACOSX directory!"
fi

echo "ALL CHECKS COMPLETED!"
