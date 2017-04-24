#!/usr/bin/env python2
# coding: utf-8

import os,socket,threading,time,signal,getopt,re
import atexit
import sys
import logging
from lxml import etree
from daemon import runner
import requests
#import traceback
DEBUG = False

def iptoint(ip):
    return int(socket.inet_aton(ip).encode('hex'),16)
    
def inttoip(ip):
    return socket.inet_ntoa(hex(ip)[2:].decode('hex'))

def log(mode,message):
    if DEBUG or mode != 'DEBUG' :
        open(log_file,"a+").write("[{}][{}] : {}\n".format(time.strftime('%Y-%m-%d %H:%M:%S'), mode, message))

def close():
    log('INFO', "ftpd stoped")

class FTPserverThread(threading.Thread):
    global cwd

    def __init__(self,(conn,addr)):
        self.conn=conn
        self.addr=addr
        for config in dataconfig.xpath("/config/daemon/ftp_dir"):
          ftp_dir = config.text
        currdir=os.path.abspath( ftp_dir)
        self.basewd=currdir
        self.cwd=self.basewd
        self.rest=False
        self.pasv_mode=False
        threading.Thread.__init__(self)

    def run(self):
        addr=self.conn.getpeername()
        log('INFO', "connect: " + addr[0])
        authorized_camera=False
        if authorized_ip_list and authorized_ip_list != "":
            log('DEBUG', "authorized : restriction " + authorized_ip_list)
            for authorized_ip_rang in authorized_ip_list.split(','):
                log('DEBUG', "authorized : try " + authorized_ip_rang)
                if re.match('.*/.*',authorized_ip_rang):
                    addresse_list=authorized_ip_rang.split('/')
                    addresse_list[1] = inttoip(iptoint(addresse_list[0]) + pow(2, 32 - int(addresse_list[1]))-1)
                    if iptoint(addresse_list[0]) <= iptoint(addr[0]) and iptoint(addr[0]) <= iptoint(addresse_list[1]):
                        log('DEBUG', "authorized : mask " + authorized_ip_rang)
                        authorized_camera=True
                elif re.match('.*\-.*',authorized_ip_rang):
                    addresse_list=authorized_ip_rang.split('-')
                    if iptoint(addresse_list[0]) <= iptoint(addr[0]) and iptoint(addr[0]) <= iptoint(addresse_list[1]):
                        log('DEBUG', "authorized : range " + authorized_ip_rang)
                        authorized_camera=True
                elif addr[0] == authorized_ip_rang:
                    log('DEBUG', "authorized : equ " + authorized_ip_rang)
                    authorized_camera=True
        else:
            log('DEBUG', "authorized : no restriction")
            authorized_camera=True

        if authorized_camera:
            log('DEBUG', "authorized camera")
            try:
                client=socket.gethostbyaddr(addr[0])
                clientdns=client[0]
            except Exception,e:
                clientdns="Addr_" + addr[0]
                log('DEBUG', "unable to solve: " + addr[0] + " " +str(e))
            log('DEBUG', "identify as: " + clientdns)
            self.cwd=os.path.join(self.basewd,clientdns)
            if not os.path.isdir(self.cwd):
                log('DEBUG', clientdns + " mkdir: " + self.cwd)
                os.mkdir(self.cwd)
                log('DEBUG', clientdns + " Force detect")
                r = requests.get(url_force_scan)
            self.conn.send('220 Welcome!\r\n')
            while True:
                cmd=self.conn.recv(256)
                if not cmd: break
                if cmd.rstrip().upper() == "QUIT":
                    self.conn.send('221 Goodbye.\r\n')
                    self.conn.close()
                    break
                else:
                    log('DEBUG', clientdns + " Recieved: " + cmd.rstrip())
                    try:
                        func=getattr(self,cmd[:4].strip().upper())
                        func(cmd)
                    except Exception,e:
                        log('ERROR', clientdns + " " +str(e) + " : " + cmd)
                        #traceback.print_exc()
                        self.conn.send("500 '" + str(e) + "': command not understood.")
        else:
            log('ERROR', "connexion refuser from " + addr[0])
            self.conn.send('223 Sorry\r\n')

    def SYST(self,cmd):
        self.conn.send('215 Jeedom Type: L8\r\n')

    def OPTS(self,cmd):
        if cmd[5:-2].upper()=='UTF8 ON':
            self.conn.send('200 OK.\r\n')
        else:
            self.conn.send('451 Sorry.\r\n')

    def USER(self,cmd):
        self.conn.send('331 OK.\r\n')

    def FEAT(self,cmd):
        self.conn.send('211 Extensions supported:\r\n')
        #self.conn.send('EPRT')
        #self.conn.send('IDLE')
        #self.conn.send('MDTM')
        self.conn.send('SIZE')
        #self.conn.send('MFMT')
        #self.conn.send('REST STREAM')
        #self.conn.send('MLST type*;size*;sizd*;modify*;UNIX.mode*;UNIX.uid*;UNIX.gid*;unique*;')
        #self.conn.send('MLSD')
        #self.conn.send('AUTH TLS')
        #self.conn.send('PBSZ')
        #self.conn.send('PROT')
        #self.conn.send('UTF8')
        #self.conn.send('TVFS')
        #self.conn.send('ESTA')
        self.conn.send('PASV')
        #self.conn.send('EPSV')
        #self.conn.send('SPSV')
        #self.conn.send('ESTP\r\n')
        self.conn.send('211 End.\r\n')

    def PASS(self,cmd):
        self.conn.send('230 OK.\r\n')
        #self.conn.send('530 Incorrect.\r\n')

    def NOOP(self,cmd):
        self.conn.send('200 OK.\r\n')

    def TYPE(self,cmd):
        self.mode=cmd[5]
        self.conn.send('200 Binary mode.\r\n')

    def CDUP(self,cmd):
        #if not os.path.samefile(self.cwd,self.basewd):
            #learn from stackoverflow
        #    self.cwd=os.path.abspath(os.path.join(self.cwd,'..'))
        self.conn.send('200 OK.\r\n')

    def PWD(self,cmd):
        cwd=os.path.relpath(self.cwd,self.basewd)
        if cwd=='.':
            cwd='/'
        else:
            cwd='/'+cwd
        self.conn.send('257 \"%s\"\r\n' % cwd)

    def CWD(self,cmd):
        # chwd=cmd[4:-2]
        # if chwd=='/':
            # self.cwd=self.basewd
        # elif chwd[0]=='/':
            # self.cwd=os.path.join(self.basewd,chwd[1:])
        # else:
            # self.cwd=os.path.join(self.cwd,chwd)
        self.conn.send('250 OK.\r\n')

    def ALLO(self,cmd):
        # chwd=cmd[4:-2]
        # if chwd=='/':
            # self.cwd=self.basewd
        # elif chwd[0]=='/':
            # self.cwd=os.path.join(self.basewd,chwd[1:])
        # else:
            # self.cwd=os.path.join(self.cwd,chwd)
        self.conn.send('250 OK.\r\n')

    def PORT(self,cmd):
        if self.pasv_mode:
            self.servsock.close()
            self.pasv_mode = False
        l=cmd[5:].split(',')
        self.dataAddr='.'.join(l[:4])
        self.dataPort=(int(l[4])<<8)+int(l[5])
        self.conn.send('200 Get port.\r\n')

    def PASV(self,cmd): # from http://goo.gl/3if2U
        self.pasv_mode = True
        self.servsock = socket.socket(socket.AF_INET,socket.SOCK_STREAM)
        self.servsock.bind((local_ip,0))
        self.servsock.listen(1)
        ip, port = self.servsock.getsockname()
        log('DEBUG', "open " + ip + " " + str(port))
        self.conn.send('227 Entering Passive Mode (%s,%u,%u).\r\n' %
                (','.join(ip.split('.')), port>>8&0xFF, port&0xFF))

    def EPSV(self,cmd): # from http://goo.gl/3if2U
        self.pasv_mode = True
        self.servsock = socket.socket(socket.AF_INET,socket.SOCK_STREAM)
        self.servsock.bind((local_ip,0))
        self.servsock.listen(1)
        ip, port = self.servsock.getsockname()
        log('DEBUG', "open " + ip + " " + str(port))
        self.conn.send('229 Entering Extended Passive Mode (|||%u|).\r\n' %
                (port&0xFF))

    def LIST(self,cmd):
        #self.conn.send('150 Here comes the directory listing.\r\n')
        #print 'list:', self.cwd
        #self.start_datasock()
        #for t in os.listdir(self.cwd):
        #    k=self.toListItem(os.path.join(self.cwd,t))
        #    self.datasock.send(k+'\r\n')
        #self.stop_datasock()
        self.conn.send('226 Directory send OK.\r\n')

    def toListItem(self,fn):
        st=os.stat(fn)
        fullmode='rwxrwxrwx'
        mode=''
        for i in range(9):
            mode+=((st.st_mode>>(8-i))&1) and fullmode[i] or '-'
        d=(os.path.isdir(fn)) and 'd' or '-'
        ftime=time.strftime(' %b %d %H:%M ', time.gmtime(st.st_mtime))
        return d+mode+' 1 user group '+str(st.st_size)+ftime+os.path.basename(fn)

    def MKD(self,cmd):
        #dn=os.path.join(self.cwd,cmd[4:-2])
        #os.mkdir(dn)
        self.conn.send('257 Directory created.\r\n')

    def RMD(self,cmd):
        #dn=os.path.join(self.cwd,cmd[4:-2])
        #if allow_delete:
        #    os.rmdir(dn)
        self.conn.send('250 Directory deleted.\r\n')
        #else:
        #    self.conn.send('450 Not allowed.\r\n')

    def DELE(self,cmd):
        #fn=os.path.join(self.cwd,cmd[5:-2])
        #if allow_delete:
        #    os.remove(fn)
        #self.conn.send('250 File deleted.\r\n')
        #else:
            self.conn.send('450 Not allowed.\r\n')

    def RNFR(self,cmd):
        #self.rnfn=os.path.join(self.cwd,cmd[5:-2])
        self.conn.send('350 Ready.\r\n')

    def RNTO(self,cmd):
        #fn=os.path.join(self.cwd,cmd[5:-2])
        #os.rename(self.rnfn,fn)
        self.conn.send('250 File renamed.\r\n')

    def REST(self,cmd):
        #self.pos=int(cmd[5:-2])
        #self.rest=True
        self.conn.send('250 File position reseted.\r\n')

    def RETR(self,cmd):
        #fn=os.path.join(self.cwd,cmd[5:-2])
        #fn=os.path.join(self.cwd,cmd[5:-2]).lstrip('/')
        #print 'Downlowding:',fn
        #if self.mode=='I':
        #    fi=open(fn,'rb')
        #else:
        #    fi=open(fn,'r')
        #self.conn.send('150 Opening data connection.\r\n')
        #if self.rest:
        #    fi.seek(self.pos)
        #    self.rest=False
        #data= fi.read(1024)
        #self.start_datasock()
        #while data:
        #    self.datasock.send(data)
        #    data=fi.read(1024)
        #fi.close()
        #self.stop_datasock()
        self.conn.send('226 Transfer complete.\r\n')

    def SIZE(self,cmd):
        self.conn.send('550 Can\'t check for file existence.\r\n')

    def STOR(self,cmd):
        self.APPE(cmd)

    def APPE(self,cmd):
        log('INFO', "Uploading: " + cmd[5:-2])
        words=cmd[5:-2].split("/")

        for word in words:
            fn=os.path.join(self.cwd, word)
        log('DEBUG', "Uploading to: " + fn)
        if self.mode=='I':
            fo=open(fn,'wb')
        else:
            fo=open(fn,'w')
        self.conn.send('150 Opening data connection.\r\n')
        if self.pasv_mode:
            self.datasock, addr = self.servsock.accept()
            clientip=addr[0]
        else:
            self.datasock=socket.socket(socket.AF_INET,socket.SOCK_STREAM)
            self.datasock.connect((self.dataAddr,self.dataPort))
            clientip=self.dataAddr
        log('DEBUG', "connect: " + clientip)
        try:
            client=socket.gethostbyaddr(clientip)
            clientdns=client[0]
        except Exception,e:
            clientdns="Addr_" + clientip
            log('DEBUG', "unable to solve: " + clientip + " " +str(e))
        log('DEBUG', "identify as: " + clientdns)
        self.cwd=os.path.join(self.basewd,clientdns)
        if not os.path.isdir(self.cwd):
            log('DEBUG', "mkdir:" + self.cwd)
            os.mkdir(self.cwd)
            log('DEBUG', clientdns + " Force detect")
            r = requests.get(url_force_scan)
        while True:
            data=self.datasock.recv(1024)
            if not data: break
            fo.write(data)
        fo.close()
        self.datasock.close()
        if self.pasv_mode:
            self.servsock.close()
        self.conn.send('226 Transfer complete.\r\n')
        log('DEBUG', clientdns + " Notify capture " + url_new_capture + '&LogicalId=' + clientdns + '&lastfilename=' + cmd[5:-2])
        r = requests.get(url_new_capture + '&LogicalId=' + clientdns + '&lastfilename=' + cmd[5:-2])

class FTPserver(threading.Thread):
    def __init__(self):
        self.sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        for config in dataconfig.xpath("/config/daemon/local_ip"):
          local_ip = config.text
        for config in dataconfig.xpath("/config/daemon/port"):
          local_port = int(config.text)
        time.sleep(1)
        self.sock.bind((local_ip,local_port))
        threading.Thread.__init__(self)

    def run(self):
        log('DEBUG', "Debug actif")
        log('DEBUG', "ftpd starting")
        log('DEBUG', "Listen " + local_ip + ":" + str(local_port))
        self.sock.listen(5)
        log('INFO', "ftpd started")
        atexit.register(close)
        while True:
            th=FTPserverThread(self.sock.accept())
            th.daemon=True
            th.start()

    def close(self):
        log('DEBUG', "ftpd stopping")
        self.sock.close()
        log('INFO', "ftpd stopped")

def handler(signum, frame):
    log('INFO', "Signal handler called with signal " + signum)
    ftp.stop()

class App():
    def __init__(self):
        self.stdin_path = '/dev/null'
        self.stdout_path = '/dev/null'
        self.stderr_path = '/dev/null'
        self.pidfile_path =  pid_file
        self.pidfile_timeout = 5

    def run(self):
        ftp=FTPserver()
        ftp.daemon=True
        ftp.start()
        while True:
#            print("Howdy!  Gig'em!  Whoop!")
            time.sleep(10)
#        raw_input('Enter to end...\n')
        ftp.stop()

if sys.argv[1] == "test":
    print("ftpd startable")
    sys.exit(0)

configfile = os.path.dirname(os.path.realpath(__file__)) + '/ftpd.xml'

dataconfig = etree.parse(configfile)
for config in dataconfig.xpath("/config/daemon/log_file"):
  log_file = config.text

log('INFO', "Ask ftpd " + sys.argv[1])

for config in dataconfig.xpath("/config/daemon/pid_file"):
  pid_file = config.text

for config in dataconfig.xpath("/config/daemon/debug"):
  if config.text != '0':
    std_log_file = log_file
    DEBUG = True
  else:
    DEBUG = False
for config in dataconfig.xpath("/config/daemon/local_ip"):
  local_ip = config.text

if not local_ip:
  log('ERROR', "local_ip not found in config file")
  sys.exit()

for config in dataconfig.xpath("/config/daemon/port"):
  local_port = config.text
if not local_port:
  log('ERROR', "local_port not found in config file")
  sys.exit()
for config in dataconfig.xpath("/config/daemon/ftp_dir"):
  ftp_dir = config.text
if not ftp_dir:
  log('ERROR', "ftp_dir not found in config file")
  sys.exit()

for config in dataconfig.xpath("/config/daemon/url_force_scan"):
  url_force_scan = config.text
for config in dataconfig.xpath("/config/daemon/url_new_capture"):
  url_new_capture = config.text
for config in dataconfig.xpath("/config/daemon/authorized_ip"):
  authorized_ip_list = config.text

if __name__ == '__main__':
  app = App()
  daemon_runner = runner.DaemonRunner(app)
  try:
    daemon_runner.do_action()
  except Exception,e:
    log('ERROR', "Unable to do " + sys.argv[1] + " " + str(e))
