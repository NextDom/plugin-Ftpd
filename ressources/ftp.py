#!/usr/bin/env python2
# coding: utf-8

from ftplib import FTP

def main():
    try:
        global FtpClient, FileDescriptor, FileName
        FtpClient = FTP()
        print("Connection")
        FtpClient.connect('127.0.0.1', 8888, 20)
        print("login")
        FtpClient.login()
        FileName = '../plugin_info/Ftpd_icon.png'
        print("openfile")
        FileDescriptor = open(FileName, 'rb') # on ouvre le fichier en mode "read-binary"
        print("sendfile")
        FtpClient.storbinary('STOR '+FileName, FileDescriptor) # envoi
        FileDescriptor.close()
        print("quit")
        FtpClient.quit()
    except TypeError:
        print "Erreur: mauvais nombre d'arguments pour '%s' command." % cmdname
    except AttributeError:
        print "Erreur : vous n'êtes pas connecté !"
    return 0

import sys

main()
