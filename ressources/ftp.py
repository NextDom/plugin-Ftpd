#!/usr/bin/env python2
# coding: utf-8

from ftplib import FTP

def main():
    try:
        global ftp, file, fichier
        ftp = FTP()
        ftp.connect('127.0.0.1', 8888)
        ftp.login()
        fichier = '../plugin_info/ftpd_icon.png'
        file = open(fichier, 'rb') # on ouvre le fichier en mode "read-binary"
        ftp.storbinary('STOR '+fichier, file) # envoi
        ftp.quit()
    except TypeError:
        print "Erreur: mauvais nombre d'arguments pour '%s' command." % cmdname
    except AttributeError:
        print "Erreur : vous n'êtes pas connecté !"
    return 0

import sys

main()
