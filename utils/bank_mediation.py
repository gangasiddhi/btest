#!/usr/bin/env python
# coding: utf-8
#
# Author: Alper Kanat <alper@butigo.com>

'''
Calculates the distribution of the banks over the
POS integrations.

Currently we use 5 POS integrations for all banks:

    1) Finansbank
    2) Garanti Bankası
    3) YapıKredi Bankası
    4) AKBANK
    5) İş Bankası

To be able to let user take advantage of installments,
we have to use the corresponding bank's POS integration
of which the customer has its card. Hence, for banks like

    * HSBC
    * ING Bank

we proxy all transactions through Finansbank. With this
script, we're able to see which transactions go through
each integration.

Data extraction is done via production transaction logs of
each integration with the following commands:

$ for i in `seq 20`; do if (( $i <= 9 )); then \
    dt="0$i-11-2012"; else dt="$i-11-2012"; fi; \
    lf="pgfmessage_$dt.log"; cat "log/pgf/$lf"|grep \
    "<Number>"; done &> ~/downloads/pgf.txt

$ for i in `seq 20`; do if (( $i <= 9 )); then \
    dt="0$i-11-2012"; else dt="$i-11-2012"; fi; \
    lf="pggmessage_$dt.log"; cat "log/pgg/$lf"|grep \
    "<Number>"; done &> ~/downloads/pgg.txt

$ for i in `seq 20`; do if (( $i <= 9 )); then \
    dt="0$i-11-2012"; else dt="$i-11-2012"; fi; \
    lf="pgymessage_$dt.log"; cat "log/pgy/$lf"|grep \
    "<ccno>"; done &> ~/downloads/pgy.txt

$ for i in `seq 20`; do if (( $i <= 9 )); then \
    dt="0$i-11-2012"; else dt="$i-11-2012"; fi; \
    lf="pgamessage_$dt.log"; cat "log/pga/$lf"|grep \
    "<Number>"; done &> ~/downloads/pga.txt

$ for i in `seq 20`; do if (( $i <= 9 )); then \
    dt="0$i-11-2012"; else dt="$i-11-2012"; fi; \
    lf="pgimessage_$dt.log"; cat "log/pgi/$lf"|grep \
    "<Number>"; done &> ~/downloads/pgi.txt

This commands outputs the credit card numbers to the
corresponding logs. Then we have to modify the file so that
we'll have the first 6 digits of the card numbers per line
like the following:

454360
534264
405917
435508

We also need to have an up-to-date list of bin_list.json
which I'll be committing together with this script. Though
mine is outdated.
'''

import json
import sys


def usage():
    print 'Usage:', __file__, \
        'path/to/bin_list.json path/to/pgf.txt path/to/pgg.txt path/to/pgy.txt path/to/pga.txt path/to/pgi.txt'


def importBinList(pathToBinList):
    binList = {}

    with open(pathToBinList) as f:
        binListStr = f.read()
        binList = json.loads(binListStr)

    return binList


def importCardNumbers(pathToList):
    ccNoList = []

    with open(pathToList) as f:
        ccNoList = f.read().splitlines()

    return ccNoList


def mediator(binList, title, ccNoList):
    md = {
        'BİLİNMEYEN': 0
    }
    unknownBins = []

    print '\n', title, '\n'

    for bin in ccNoList:
        if bin in binList:
            if binList[bin]['bankName'] in md:
                md[binList[bin]['bankName']] += 1
            else:
                md[binList[bin]['bankName']] = 0
        else:
            md['BİLİNMEYEN'] += 1
            unknownBins.append(bin)

    for b, c in md.iteritems():
        print b, '->', c

    if len(unknownBins):
        print u'\nBilinmeyen Bin Numaraları\n'
        print unknownBins


if __name__ == '__main__':
    if len(sys.argv) == 1 or len(sys.argv) < 4:
        usage()
        sys.exit(1)

    pathToBinList = sys.argv[1]
    pathToPGF = sys.argv[2]
    pathToPGG = sys.argv[3]
    pathToPGY = sys.argv[4]
    pathToPGA = sys.argv[5]
    pathToPGI = sys.argv[6]

    binList = importBinList(pathToBinList)
    pgf = importCardNumbers(pathToPGF)
    pgg = importCardNumbers(pathToPGG)
    pgy = importCardNumbers(pathToPGY)
    pga = importCardNumbers(pathToPGA)
    pgi = importCardNumbers(pathToPGI)

    mediator(binList, u'Finansbank WebPOS', pgf)
    mediator(binList, u'Garanti SanalPOS', pgg)
    mediator(binList, u'YapıKredi PosNet', pgy)
    mediator(binList, u'AKBANK SanalPOS', pga)
    mediator(binList, u'İş Bankası SanalPOS', pgi)
