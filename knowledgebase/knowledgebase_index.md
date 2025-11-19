# IT Solutions Quick Reference Index

Total Solutions: 647

Use Ctrl+F / Cmd+F to search for keywords

---

| ID | Title | Description Preview |
|----|-------|--------------------|
| 5 | Forward DC@appshosting.com emails to helpdesk@appshosting.com | Logged into http://mail.appshosting.com with dc@appshosting.com pass= Mynewpc30 Went to thr rules wizard and created a forwarding rule that sends all  |
| 6 | Sharepoint Drive Mapping | https://appshostingcom-1.sharepoint.microsoftonline.com/Infrastructure/Forms |
| 8 | DSI Unity Phone System | The attached document describes how to log into DSI's Unity Phone system. |
| 9 | Error: ESX1 | Delted some older backups on driveds F and G of IT-Srvr. |
| 10 | Install and configure Oracle Enterprise Manager | Install and configure Oracle Enterprise Manager and Monitoring Agent. OEM Info: server: oem.appshosting.com secure URL: https://oem.appshosting.com:11 |
| 11 | Linux email Relay Server | Add the following line to sendmail.mc FEATURE(promiscuous_relay)dnl before the following lines MAILER(smtp)dnl MAILER(procmail)dnl dnl MAILER(cyrusv2) |
| 12 | chroot functinality in sftp | Let's assume you already have ssh set up and you want (chroot-sftp). So now we'll start configuring the second sshd daemon for sftp access: 1. cp -p / |
| 77 | Configuration of Windows 2008 Server - Radius for PIX VPN | Options Symptom: (public) I like connecting to my network using my pfSense firewall's built-in VPN server. Following these steps, I can configure Wind |
| 340 | Out of memory error sb-db\r\nkernel: EXT3-fs error (device dm-5) in start_transaction: Journal has a | Ashok, CAUSE : Out of memory Error Feb 2 05:11:01 sb-db kernel: Out of Memory: Killed process 20863 (oracle). Feb 2 05:12:45 sb-db kernel: oom-killer: |
| 13 | Cisco ASA 5520 SmartNet Support Information | Cisco Smartnet Information CONTRACT INFORMATION Service Level: IPS Svc, AR NBD(SU1) Reseller PO Number: 70F0654D Cisco Quote Number: Q11766808 Registe |
| 37 | How to add DADS entry for APEX | Options Symptom: (public) How to add DADS entry for APEX Problem: (public) How to add DADS entry for APEX Solution: (public) For Production on ah3: su |
| 372 | Ask for disable trigger in Production (for Global timezone issue) | alter trigger apps.TPC_ASO_QUOTE_HEADERS_T2I disable; |
| 15 | http://onlinetest.net4practice.com/login website info | In prod we have may tomcat processes running on differenet ports. For http://onlinetest.net4practice.com/login , the tomcat is running on port 8880. T |
| 17 | Cisco SSM-10 IPS Login | Upgraded the IPS to 7.0 (2) The IPS ip address is 172.1.0.52 To connect https://172.1.0.52 Username : cisco Password : a0droot2oo6! |
| 18 | Opening Ports on Cisco Firewall (ASA or Pix) | Opening ports on ASA You need to know the internal and external IP and ports. Log into ASA (ssh 172.1.0.1, default gateway) "enable" mode (type ena) " |
| 19 | DSI DNS Logins | dyndns: customer: dispensingsolutions ashok / wltd0ww Network Solutions hvolkman dsi-4321! |
| 20 | Set Maximum Bandwidth Throughput Speed | The policy configuratio is set on the Cisco ASA: policy-map police-policy class class-default police output 12583000 police input 12583000 |
| 23 | Changing DB privilaged accounts passwords which are going to expire when logging to db console | Please follow the following metalink notes to reset the passwords for dbsnmp, sysman and mgmt_view. How to change the password of the 10g database use |
| 24 | Customer worksheet | This document contains customer and their environment information. It covers most shared and small customers. Large customers have their own individua |
| 25 | Add CUSTOM_TOP for R12 environment for RS group | Login to ah200 as oravis Create new file $APPL_TOP/customVIS_ah200.env Add following content in this file XXRS_TOP=/d01/oracle/VIS/apps/apps_st/appl/x |
| 26 | Create APEX workspace manually | See attached Sample APEX Schema creation script: 1. Create tablespace for data and index: create tablespace abc_data datafile '/u01/oradata/ahdb07/abc |
| 27 | Provision new CPANEL account | Provision new CPANEL account |
| 365 | Active Directory and Domain Controller | ah59 - primary DC and AD ah55 - secondary DC and AD Domain: ah.local When logging in, enter ah\\ When they log in ts01 or ah35 |
| 36 | Error starting HTTP Server that comes with 10g Database Companion | Options Symptom: (public) Error starting HTTP Server that comes with 10g Database Companion /u01/app/oracomp/product/10.2.0/db_comp/opmn/logs: ------- |
| 628 | OECC post clone steps | -bash-4.2$ java oracle.apps.fnd.security.AdminDesktop apps/YNeg1bVIUpVYXj6I CREATE NODE_NAME=rhtstebsapp2.appsforte.com DBC=/u01/oracle/uat/fs2/inst/a |
| 632 | Steps to run Trial Balance Report Fix | Reference: Severity 3 SR 3-24762381491 : AP Trial Balance is not removing fully paid transactions Step # 01 (Apply Patch): ==================== **NOTE |
| 28 | How to remove APEX workspace | Remove APEX workspace 1. Remove APEX workspace 1.1 login to APEX admin console http://ah3.appshosting.com:8004/apex/apex_admin Username: admin Passwor |
| 29 | Migrating or copy APEX and database schema | Need to migrate APEX schema from one database to another which is on different database and different tablespace Solution: (public) Use expdp/impdp to |
| 30 | SSH tunneling to 11g APEX database | To access database from client tool including sqlplus or sqldeveloper 1. Login to prod.appshosting.com as your Unix account 2. Setup SSH tunneling in  |
| 31 | APEX 3.2.1 Upgrade vs. Install | Options Symptom: (public) APEX 3.2.1.00.10 Upgrade vs. Install Problem: (public) APEX 3.2.1.00.10 Upgrade vs. Install Solution: (public) Pre-Upgrade/I |
| 32 | APEX and Active Directory integration | Options Symptom: (public) Problem: (public) Solution: (public) 525 - user not found 52e - invalid credentials 530 - not permitted to logon at this tim |
| 34 | Cisco Switch 3550 VLAN Configuration | (public) Configure the VLAN on Catalyst 3550, 3750, 4500/4000, and 6500/6000 Switches That Run Cisco IOS Software Create VLANs and Ports This section  |
| 35 | Config XMLDB | Options Symptom: (public) User request to set xml db in non-default port Problem: (public) User request to set xml db in non-default port Solution: (p |
| 615 | List or check applied patches on EBS 12.x | R11i / R12.0 / R12.1 / R12.2: Oracle Applications Current Patchset Comparison Utility - patchsets.sh (Doc ID 139684.1) E-Business Suite Applications M |
| 629 | Save large table to .CSV | Create below procedure under sys schema and then execute the exec command to export the table dump in CSV format -- Generate dump file without double  |
| 597 | Clone schedule 360 RAC to RAC | Reference: STEP BY STEP RMAN DUPLICATE Database From RAC ASM To RAC ASM (Doc ID 1913937.1) Source (PROD) Environment: RAC -- 3 Nodes af348.appsforte.c |
| 373 | Package Issue | Bounced Apache and database on ah118. Then re-created the package using /home/oraapex/sql/payments_pkg.sql script. |
| 91 | How to enable Huge Pages on Red Hat/CentOS LInux VM or physical box? | Options Symptom: (public) How to improve Oracle database Performance in either physical system or Virtual system? Problem: (public) How to enable huge |
| 117 | New server installation checklist | Options Attachment: New server installation checklist.xls 14.0 KBytes Symptom: (public) Problem: (public) Solution: (public) |
| 38 | How to configure Oracle HTTP Server for APEX | Options Symptom: (public) Need to configure free Oracle HTTP Server for APEX, comes with 10g database Companion CD Problem: (public) Need to configure |
| 40 | How to enable SSL for APEX applicatoin | http://download.oracle.com/docs/cd/B15897_01/core.1012/b13995/wallets.htm#ASADM401 http://download.oracle.com/docs/cd/B15897_01/core.1012/b13995/walle |
| 41 | How to remove APEX workspace | Options Symptom: (public) Remove APEX workspace Problem: (public) Solution: (public) Remove APEX workspace 1. Remove APEX workspace 1.1 login to APEX  |
| 42 | How to set up APEX print using Apache FOP | Options Attachment: fop1.jpg 95.7 KBytes fop2.jpg 46.9 KBytes apex print.jpg 45.6 KBytes Symptom: (public) Need to print/generate PDF from APEX Proble |
| 43 | LDAP authentication from Linux to windows AD | Options Attachment: LDAP AUTH TO WINDOWS SERVER ACTIVE DIRECTORY FROM LINUX.doc 75.5 KBytes Symptom: (public) LDAP authentication from Linux to window |
| 636 | Pulling PO_ID from po_header_id table. | Please find the steps below; 1) Get the concurrent program name PO Output for Communication. select * from All_TABLES where table_name like 'PO_HEADER |
| 563 | Vpn client for mac | Problem : Client faced problem installing VPN client on Mac References : https://drive.google.com/open?id=0BwbkrP97qT3bZTBPWVhrck95eDg Symptoms : Inst |
| 44 | Migrating or copy APEX and database schema | Options Symptom: (public) Need to migrate APEX schema from one database to another Problem: (public) Need to migrate APEX schema from one database to  |
| 46 | Oracle APEX log-in throwing Server Unavailable error | Oracle APEX log-in throwing Server Unavailable error. Apache error.log showing: [error] [client 192.168.10.61] [ecid: 1251738027:127.0.0.1:5940:0:5,0] |
| 47 | Procedure for copying APEX schema and blobs from one database to another | Options Symptom: (public) When copying Apex schemas with blobs to new database, the blobs do not copy over via exp/imp or expdp/impdt. Problem: (publi |
| 48 | Refresh APEX schema | Options Symptom: (public) Customer requests to refresh from one APEX schema to another schema Problem: (public) Solution: (public) 1. Find out schemas |
| 50 | Troubleshoot opmn startup libdb error | Options Symptom: (public) When startup opmn service in Oracle iAS 10.1.3.3, getting error related to libdb Problem: (public) 09/01/21 01:26:17 Start p |
| 537 | Set up OCFS2 | Set up Oracle Clustered File System v2 (OCFS2) Nano staging app servers, we have added 30G disk 10.100.100.182 and 113 disk - /dev/xvdc1 |
| 57 | How to restart Greeting Cards service | Options Symptom: (public) Greeting-cards.com not available Problem: (public) Greeting-cards.com down Solution: (public) 1. Log in to gc2.appshosting.c |
| 51 | Upgrade to Application Express 3.2 | Options Attachment: apex32 upgrade.swf.html 708 Bytes apex32 upgrade.swf 2.0 MBytes Symptom: (public) Problem: (public) Upgrade to APEX 3.2 Solution:  |
| 128 | Root O/S Backup procedures | Options Symptom: (public) ROOT Backup mkdir /b01/os/`hostname` (if necessary) IF ROOT PARTITION is GREATER THAN 50gb nohup tar cvf /b01/os/`hostname`/ |
| 52 | Apache SSL setup on Windows | Options Symptom: (public) Problem: (public) Solution: (public) C:\\Program Files\\Apache Software Foundation\\Apache2.2\\bin>openssl OpenSSL> '' opens |
| 53 | CPanel - httpd.conf Apache Adding Custom Directives | Options Symptom: (public) Adding Custom Directives to httpd.conf Many users are initially daunted by the new system used for generating and managing A |
| 54 | Convert SSL Certificate to PFX (Windows) | Options Symptom: (public) openssl pkcs12 -export -out NEW.pfx -inkey ../private/\\*.appshosting.com.key -in \\*.appshosting.com.crt -certfile bundle.c |
| 55 | How to generate an SSL CSR through CPanel | Generate CSR (Certificate Signing Request) 1. Request SSL site information from customer: Country: USA (US?) State: Utah Locality: Park City Organizat |
| 56 | How to install SSL certificate in CPANEL | Options Attachment: Install SSL certificate.zip 2.9 MBytes Symptom: (public) Install new SSL certificate in CPANEL Problem: (public) Solution: (public |
| 633 | oracle Servlet error: An exception occurred. The current application deployment descriptors do not a | 1. Stop oacore 2. Clear persistence folder cd $INST_TOP/ora/10.1.3/j2ee/oacore; rm –rf persistence 3. Start oacore |
| 59 | ADDING A VIRTUAL HOST in Apache for Fadi, and others using AH1 server | Options Symptom: (public) ADDING A VIRTUAL HOST in Apache for Fadi, and others using AH1 server Added the following directives in /etc/httpd/conf/vhos |
| 60 | AH1 - Start Web / Proxy Server | Options Symptom: (public) root@ah1 [~]# /etc/init.d/httpd stop Stopping httpd: [ OK ] root@ah1 [~]# /etc/init.d/httpd start Starting httpd: [Mon Oct 1 |
| 61 | Add Swap Space to Redhat Linux Server | Options Symptom: (public) To add a swap file: Determine the size of the new swap file and multiple by 1024 to determine the block size. For example, t |
| 62 | Allow new IP access to server through VPN | Options Symptom: (public) Problem: (public) Solution: (public) [sam@ah23 ~]$ ssh 172.1.0.1 The authenticity of host '172.1.0.1 (172.1.0.1)' can't be e |
| 63 | Apache - Force HTTPS / SSL | Options Symptom: (public) Add the following in the virtualhosts directive in httpd.conf: RewriteEngine On RewriteCond %{HTTPS} off RewriteRule (.*) ht |
| 64 | Apache PHP MySQL OCI8 How to? | Options Attachment: oracle-instantclient11.1-devel-11.1.0.7.0-1.x86_64.rpm 565.4 KBytes oracle-instantclient11.1-devel-11.1.0.7.0-1.i386.rpm 565.3 KBy |
| 126 | Restart Tomcat on CPanel Server (prod) | Options Symptom: (public) Login as root and execute: root@prod [~]# /usr/sbin/stoptomcat root@prod [~]# /usr/sbin/starttomcat Problem: (public) Soluti |
| 65 | Apache SSL Multiple Virtual Hosts | Options Symptom: (public) Use a configuration like the following, terminating SSL on multiple ports for multiple SSL virtual hosts: LoadModule ssl_mod |
| 66 | Backup Mount Points - Mounting NFS /b01 /b02 /b03 | Options Symptom: (public) /etc/init.d/nfs start /etc/init.d/nfslock start mkdir /b01 /b02 /b03 filer.appshosting.com:/b01 /b01 nfs rw,bg,hard,nointr,t |
| 67 | Big Brother - Purple Alerts | Options Symptom: (public) Big Brother: 1. First Login to the server 2. Next browse to BB logs directory. 3. Next run the command grep -i purple 4. Fin |
| 68 | Big Brother FileSystem FULL issue | (public) Big Brother complains that the File system is FULL on new RHEL/CentOS 5 clients Problem: (public) Big Brother complains that the File system  |
| 626 | Add Oracle Database ACL | Title : Add Database ACL rule References : Procedure or Description : [af3:/home/oraapex@af3 ~]$ more jb_acl.sql BEGIN DBMS_NETWORK_ACL_ADMIN.create_a |
| 69 | Broadcom BCM5716 Nic driver build on RHEL 5.2 | Options Symptom: (public) On new R410 servers RHEL 5.2 will not identify the network card hence will not be able to bring the system online. Problem:  |
| 503 | Fix for Duplicate Tickets from Bouncing E-mails | Problem: Duplicate tickets are being generated repeatedly from bouncing e-mails Symptom: When ticket generating e-mail addresses are copied from Helpd |
| 78 | Configure ManageEngine OpManager | Option 1: Updating monitoring settings for single server/device 1. Log in to http://om.appshosting.com using admin and password. 2. Search for your se |
| 149 | How To Drop, Create And Recreate DB Control In A 10g | Options Symptom: (public) Drop, Create And Recreate DB Control In A 10g Problem: (public) Drop, Create And Recreate DB Control In A 10g Solution: (pub |
| 79 | Configuring Cisco Pix Site to Site VPN | Options Symptom: (public) isakmp policy 10 authentication pre-share isakmp policy 10 encryption 3des isakmp policy 10 hash md5 isakmp policy 10 group  |
| 70 | Brocade Switch Zoning and Multipath Configuration | Options Symptom: (public) Brocade Switch Zoning and Multipath Configuration Check the HBA names cd /sys/class/fc_host Find out port names for both HBA |
| 624 | New SFTP Notification for Brightfield | Title : Change SFTP recipient References : Procedure or Description : 1. Created custom script check_new_ftp_files_qamentor.sh 2. Add to cron. */15 *  |
| 71 | Cisco Catalyst Switch Telnet Message "Password required, but none set"\r\n | Options Symptom: (public) Background Information If you try to telnet to a router that does not have a Telnet password configured, you receive this er |
| 72 | Cisco PIX VPN Account Setup | Options Attachment: Cisco VPN Setup.rtf 10.9 MBytes Symptom: (public) Problem: (public) Solution: (public) Create Cisco VPN Account for End Users Step |
| 73 | Cisco Pix - DMZ (Mail) Interface to Inside Interface Communication | Options Symptom: (public) same-security-traffic permit inter-interface static (inside,mail) 172.1.0.105 172.1.0.105 netmask 255.255.255.255 static (ma |
| 74 | Cisco Pix ASA 5520 Password Recovery - DSI | Options Symptom: (public) Performing Password Recovery for the ASA 5500 Series Adaptive Security Appliance To recover from the loss of passwords, perf |
| 75 | Cisco Switch Firmware Upgrade (Cisco Catalyst 3550) | Options Symptom: (public) Introduction This document explains the step-by-step procedure to upgrade the software image on Cisco Catalyst 3550 series s |
| 76 | Commtting snapshots when there are no snapshot entires in the snapshot manager | Options Symptom: (public) There are some delta snapshot files left on the disk The snapshot manager shows no snapshots but there are one or more sets  |
| 80 | Create VM server and load 64bit OS | Options Attachment: Create VM server and load 64bit OS.doc 1.3 MBytes Symptom: (public) Install 64bit Linux on VM server Problem: (public) Install 64b |
| 630 | Changing Agile application administrator password from database | SQL> update agileuser set login_pwd='LD0RK6THOLTE9IIBD6RVO40L8L5OA1EK' where loginid = 'administrator'; 1 row updated. SQL> commit; Commit complete. A |
| 81 | Data Center Worklist | Options Symptom: (public) Problem: (public) Solution: (public) Comment: (internal) Completed DSI Network Changeover Fusion Troubleshooting for Josh Cu |
| 82 | Enable FTP service on Linux server | Options Symptom: (public) Enable FTP service on Linux Problem: (public) Enable FTP service on Linux Solution: (public) # /etc/init.d/vsftpd start Star |
| 83 | Enable firewall on PIX or server | Options Symptom: (public) Problem: (public) Need to open firewall for e-business or database public access Solution: (public) Open firewall on servers |
| 181 | How to clone Rel 12 | Options Symptom: (public) How to clone Problem: (public) Need to clone R12 Solution: (public) 1. Run pre-clone. 2. Copy files. 3. Rapid Clone. |
| 118 | O/S Cloning Procedures | Options Attachment: OS Cloning.docx 15.6 KBytes Symptom: (public) Problem: (public) Solution: (public |
| 119 | OS patching on 64/32 bit Linux 5 for Oracle EBS | Options Symptom: (public) Apply OS patching for 32bit Linux 5 for Oracle E-business application Problem: (public) Solution: (public) OS patching for L |
| 84 | Enable large memory support on 32 bit Linux | Options Symptom: (public) 32bit Linux is not able to recognize physical RAM beyond 4GB Problem: (public) 32bit Linux is not able to recognize physical |
| 85 | Expand Linux Filesystem Space | Options Attachment: Expand-Linux-Filesystem.doc 179.5 KBytes Symptom: (public) Expand Linux Filesystem Space Problem: (public) Solution: (public) |
| 86 | How to Clear Cisco Pix VPN Tunnel | Options Symptom: (public) fw01(config)# sh cry isa sa Active SA: 3 Rekey SA: 0 (A tunnel will report 1 Active and 1 Rekey SA during rekey) Total IKE S |
| 87 | How to SME Server with Affa ESXi Integration | Options Symptom: (public) This is how to article on SME server and Affa setup Problem: (public) This document describes the procedure to setup and imp |
| 88 | How to block SSH on Cisco PIX Firewall | Options Symptom: (public) >First: Exceptions access-list block_ssh permit tcp host any eq 22 access-list block_ssh permit tcp host any eq 22 access-li |
| 89 | How to change hostname in Linux | Options Symptom: (public) Hostname incorrect Problem: (public) Need to change host name Solution: (public) Modify the following with proper hostname:  |
| 90 | How to create cron job in CPANEL | Options Symptom: (public) How to create cron job in CPANEL? Problem: (public) Solution: (public) 1. Logon to CPANEL at http://prod.appshosting.com/cpa |
| 92 | How to enable caching for images on Apache?\r\n | Options Symptom: (public) To improve Apache performance, enable caching for the images Problem: (public) How to enable caching on Apache for images on |
| 93 | How to install VMWare License | Options Symptom: (public) License Code: 13207-4AJ5H-06C08-0KAL4-9LY7M Log into VMWare infrastructure client. Click "Configuration" Click "Licensed Fea |
| 94 | How to kill a hung VM (which is stuck at 95% on power down)\r\n | Options Symptom: (public) Unable to shutdown the VM ( example AH43) which shows 95% on task progress Problem: (public) Unable to shutdown a VM? Soluti |
| 95 | How to manage printers on Linux | Options Symptom: (public) The Procedure needs to be followed when managing printers in Linux Problem: (public) Manage printers in linux Solution: (pub |
| 96 | How to monitor Cisco PIX Firewall for SSH Connections | Options Symptom: (public) access-list capture permit tcp any any eq 22 capture ssh_in access-list capture interface inside Problem: (public) Solution: |
| 116 | NFS Mount Options for Oracle | Options Symptom: (public) In /etc/fstab file - on destination server ah7-l.appshosting.com:/backup /backup nfs rw,bg,hard,nointr,tcp,vers=3,timeo=300, |
| 97 | How to restore a file from AFFA backups | Options Attachment: How to restore a file from the AFFA backups.docx 10.2 KBytes Symptom: (public) How to restore a file from the AFFA backups Log int |
| 111 | Linux FibreChannel (SAN) Useful Commands | Options Symptom: (public) http://kbase.redhat.com/faq/docs/DOC-9937 Problem: (public) Solution: (public) |
| 418 | Checking WebLogic Admin Server Memory Usage | You can get memory usage info from WLS console as follows: 1. Log in to WLS console as weblogi.c 2. Click Environment -> Servers. 3. Click AdminServer |
| 98 | How to run both Cisco and Juniper VPN client on same machine | Options Symptom: (public) How to run both Cisco and Juniper VPN client on same machine Problem: (public) How to run both Cisco and Juniper VPN client  |
| 99 | IP Allocations DC wise | Options Attachment: NetConf_AH_20091113.xls 121.0 KBytes Symptom: (public) Please find the present IP allocations attached .. Problem: (public) Soluti |
| 100 | IP Allocations or Server details | Options Attachment: Servers in 172.1.x.x.xls 58.5 KBytes Symptom: (public) This information is mostly above 99% accurate and I will have a manual audi |
| 101 | Implement FreeDup | Options Symptom: (public) How to install Problem: (public) How to save disk space for backups? Solution: (public) Backup with Affa and FreeDup From SM |
| 102 | Import and Export from Active Directory | Options Symptom: (public) Step-by-Step Guide to Bulk Import and Export to Active Directory This guide introduces batch administration of the Active Di |
| 103 | Install Big Brother client on Linux servers | Options Symptom: (public) Install BB client Problem: (public) Solution: (public) Installing Big Brother Client on Linux server Creat bb user on client |
| 104 | Install Big Brother client on Linux servers | Options Symptom: (public) Install BB client Problem: (public) Solution: (public) Installing Big Brother Client on Linux server Creat bb user on client |
| 105 | Install and Configure Tripwire | Options Symptom: (public) refer to docs: http://www.akadia.com/services/tripwire.html http://www.redhat.com/docs/manuals/linux/RHL-9-Manual/ref-guide/ |
| 106 | Installing and Configuring a iDRAC Enterprise on a Dell R410 | Options Attachment: Installing DRAC Enterprise in a Dell Poweredge R410.doc 5.6 MBytes Symptom: (public) Problem: (public) Solution: (public) |
| 107 | LSOF Linux - How to check what process is running on what port | Options Symptom: (public) # lsof -i -nP \| grep httpd httpd 2318 apache 16u IPv4 0x019922bc 0t0 TCP 127.0.0.1:8000 (LISTEN) httpd 2319 apache 16u IPv4 |
| 108 | LVM Software RAID Administration | Options Symptom: (public) How to setup Linux LVM in 3 minutes at command line? Login with root user ID and try to avoid using sudo command for simplic |
| 109 | Linux Active Directory Integration - Windows Domain | Options Symptom: (public) Join Linux Workstations to Active Directory: PAM Samba and winbind provide authentication and identity resolution for Linux  |
| 110 | Linux Cloning with Mondo from Physical to Virtual on VMWare | Options Symptom: (public) How to clone Linux from Physical to VM or another hardware? Problem: (public) How to clone Linux OS from one hardware to ano |
| 464 | Vertex Startup Procedure | login as oravertex/vertexora123 in app node. 1. stop: /u01/VERTEX/tomcat/bin/shutdown.sh 2. Start /u01/VERTEX/tomcat/bin/startup.sh 3. review Log /u01 |
| 112 | Map a network drive in Windows ( Samba Share on Linux Server ) | Options Symptom: (public) How to Map a network drive in Windows ( Samba Share on Linux Server ) Problem: (public) Solution: (public) LINUX SERVER SIDE |
| 113 | Microsoft Support - Contacting | Options Symptom: (public) Microsoft Support Procedure Call 800-765-7768 and get an access ID. Tell them u are a MAPS Action Pack subscriber and are op |
| 114 | Mondorestore working RPM sets for x86_64 RHEL 5.2 | Options Attachment: afio-2.4.7-1.x86_64.rpm 62.1 KBytes buffer-1.19-1.x86_64.rpm 10.6 KBytes mindi-2.0.3-1.rhel5.x86_64.rpm 257.7 KBytes mindi-busybox |
| 115 | NFS Backup Mount Points (b01 b02 b03)\r\n | Options Symptom: (public) SET UP BACKUP NFS MOUNTS (b01 b02 b03) /etc/init.d/nfs start /etc/init.d/nfslock start mkdir /b01 /b02 /b03 filer.appshostin |
| 120 | Quest Big Brother Professional Support | Options Symptom: (public) Quest Login ashb@appshosting.com n3wadmmn1 http://www.quest.com Problem: (public) Solution: (public) |
| 121 | Redhat Linux - Enable RSH and RLOGIN | Options Symptom: (public) Add 'rsh' and 'rlogin' to /etc/securetty Add all IPs to /root/.rhosts, and chmod 600 /root/.rhosts Set "disable = no" in /et |
| 122 | Redhat Linux - Setting Time / NTP | Options Symptom: (public) rdate -s tick.greyware.com Configure NTP: # chkconfig ntpd on # ntpdate pool.ntp.org # /etc/init.d/ntpd start # Verify Time  |
| 123 | Reset Moodle Password for "admin" | Options Symptom: (public) Log into cdb1 mysql -u moodle -p moodle (m00m001) mysql> update mdl_user SET password=md5('m00m001') where username='admin'; |
| 124 | Reset Windows Terminal Server Session | Options Symptom: (public) Query the sessions qwinsta /server:cp-its-soadv01 Reset the sessions rwinsta /server:cp-its-soadv01 2 Problem: (public) Solu |
| 125 | Reset lost Mysql root Password | Options Symptom: (public) Linux Users: Log on to your Linux machine as the root user. The steps involved in resetting the MySQL root password are to s |
| 127 | Restore files from AFFA Backups | Options Symptom: (public) How to restore a file from the AFFA backups Log into "smesrv01.appshosting.com" as root. Look for the backups in /var/affa/  |
| 129 | SAN-009 iSCSI Configuration Instructions | Options Attachment: SAN-009-iSCSI.xls 11.5 KBytes Symptom: (public) Problem: (public) Solution: (public) |
| 130 | Sendmail Configuration behind Irvine Firewall | Options Symptom: (public) > If the server is in Irvine, please point the Sendmail "smart relay" to > ah59.appshosting.com. > > Example: /etc/mail/send |
| 131 | Sendmail MRTG Configuration | Options Symptom: (public) Graphing mailstats with MRTG This document will outline the steps to graph Sendmails basic mail stats on another box runnin |
| 132 | Setting up Sendmail Log Analyzer | Options Symptom: (public) This is How to setup sendmail log Analyzer on Unix/Linux Problem: (public) To setup sendmail log analyzer upon customer requ |
| 465 | Nanometrics Clone Document - OLD | Nanometrics Clone 01-FEB-2018 : Transaction managers setup after clone : https://docs.google.com/document/d/1Ssir5ZzmPaaKzgO_I8Y8I6PQiWm8qRTdxdcNTRR28 |
| 133 | Setup and logon to Raritan console | Options Attachment: LogOntoServerConsoles_swf.zip 6.4 MBytes Symptom: (public) Problem: (public) Solution: (public) |
| 134 | Solaris XSCF Configuration | Options Symptom: (public) Sun XSCF showhardconf showdomainstatus -a showdomainstatus -d 00 poweron -d 0 To Connect to a domain XSCF> console -d domain |
| 393 | Restart Tomcat on ah217 for vinrare and atcostflights | Login as root user to ah217.appshosting.com root / T1.... vinrare #cd /home/oak/apache-tomcat-6.0.29/bin #./shutdown.sh #./startup.sh atcostflights #c |
| 135 | Synchronize the packages between two servers and compare the packages and install oracle prerequisit | Options Attachment: script1.txt 2.5 KBytes script2.txt 2.7 KBytes Symptom: (public) If you want the packages installed on one server, the same package |
| 144 | snmpwalk installation | Options Symptom: (public) yum install net-snmp-utils Problem: (public) Solution: (public |
| 136 | Tomcat Cpanel - Adding Database Connections for Users | Options Symptom: (public) Create a "WEB-INF" directory in the App root, and create the following file, and add a database connection setting for each  |
| 137 | VMWare Installation | Options Attachment: VMWare Installation.pdf 513.5 KBytes Symptom: (public) Problem: (public) Install VMWare Solution: (public) See attached procedure  |
| 138 | Verizon Blackberry Reset Phone - Activation | Options Symptom: (public) *228 option 1 If activation doesn't work, try resetting... Problem: (public) Solution: (public) |
| 49 | SSH tunneling to 11g APEX database | Options Attachment: SSH tunneling to shared database.doc 93.5 KBytes Symptom: (public) Problem: (public) Customers need to access shared 11g APEX data |
| 140 | decrypt the csr certificate | Options Symptom: (public) decrypt the csr certificate to know the information in that file. Problem: (public) Solution: (public) #openssl req -in eap. |
| 141 | decrypt the csr certificate | Options Symptom: (public) decrypt the csr certificate to know the information in that file. Problem: (public) Solution: (public) #openssl req -in eap. |
| 142 | iSCSI Initiator Configuration | Options Attachment: ISCSI Initiator Configuration.doc 33.5 KBytes Symptom: (public) Problem: (public) Solution: (public |
| 143 | mondo backup and restore procedure | Options Attachment: afio-2.4.7-1.x86_64.rpm 62.1 KBytes buffer-1.19-1.x86_64.rpm 10.6 KBytes mindi-2.0.3-1.rhel5.x86_64.rpm 257.7 KBytes mindi-busybox |
| 145 | 11g R2 RAC Database Installation | Options Symptom: (public) Problem: (public) Solution: (public) dba:x:500: oinstall:x:501: oper:x:502: asmadmin:x:503: asmdba:x:504: asmoper:x:505: oaa |
| 466 | EBS log-in page throws session no longer active error | Problem: EBS log-in page throws below error (in IE8). You are trying to access a page that is no longer active. - The referring page may have come fro |
| 146 | Anonymous block for granting resolve privilege to a user | Options Symptom: (public) In the below example resolve privilege is given to medidata user for executing utl_inaddr.get_host_name() , reference metali |
| 147 | Clone database through RMAN duplicate | Options Symptom: (public) Clone database using RMAN duplicate Problem: (public) Solution: (public) 1. Prepare init file DB_FILE_NAME_CONVERT so that i |
| 148 | Error applying log to standby database | Options Symptom: (public) Error applying log to standby prdn Problem: (public) Error applying log to standby prdn Solution: (public) 1. log in as orac |
| 150 | How to find all roles granted to a database user | Options Symptom: (public) Problem: (public) Solution: (public) How to find all roles granted to a database user From sqlplus, run: set head off set pa |
| 151 | How to fix DR sync error due to missing data file | Options Symptom: (public) Applying archive on standby fails with the following: DR[oracle@cdb1 bin]$ tail applylog.log Specify log: { =suggested \| fi |
| 152 | How to identify sessions executing stored programs | Options Symptom: (public) Hangs when trying to drop stored programs in database Problem: (public) Session executing program still Solution: (public) c |
| 172 | Clean up node for clone | Options Symptom: (public) concurrent managers not starting up after clone Problem: (public) Solution: (public) SQL> exec fnd_conc_clone.setup_clean; P |
| 153 | How to recreate TEMP tablespace to smaller size | Options Symptom: (public) temp filled up Problem: (public) Solution: (public) SQL> create temporary tablespace temp2 tempfile '/u01/oracle/oradata/cdb |
| 154 | How to set up Shared Server Listener (formerly MTS) Configuration | Options Symptom: (public) Problem: (public) Solution: (public) Prestep: Determine how many sessions you want to support in the database using shared s |
| 155 | How to wrap PL/SQL code and export | Options Symptom: (public) PL/SQL code shows in plain text in xxx_source. Problem: (public) Need to protect PL/SQL code. Need to send out wrapped code. |
| 156 | Information on Temporary tablespace | Options Symptom: (public) Problem: (public) Solution: (public) To see the default temporary tablespace for a database, execute the following query: SQ |
| 492 | JDK 8 and Tomcat 8 Installation | JDK 8 and Tomcat 8 Installation JDK 1.8.0_112 Installation: 1. Download JDK version from http://www.oracle.com/technetwork/java/javase/downloads/jdk8- |
| 567 | Create AnyConnect VPN Users | References : Procedure or Description : 1. Log in to AD server by RDP, 10.100.90.120. 2. Choose appropriate group (folder) under VPN_Users. 3. Create  |
| 182 | How to compile forms/library in R12 | Options Symptom: (public) Problem: (public) Solution: (public) frmcmp module=/EBSr12/oracle/dev/apps/apps_st/appl/au/12.0.0/resource/USER_RCVRCERC.pll |
| 173 | Connection is dropping frequently | Options Symptom: (public) Connection is dropping frequently Problem: (public) Connection is dropping frequently Solution: (public) 1. Change networkRe |
| 157 | Install RMAN backup scripts | Options Attachment: ah_install.tar 120.0 KBytes Symptom: (public) Install RMAN backup scripts Problem: (public) Install RMAN backup scripts Solution:  |
| 637 | SFTP Account for saftp server | Title : Create new SFTP account for saftp References : Symptoms (if applicable) : Causes (if applicable) : Procedure or Solution Description : [root@s |
| 159 | MTS and Oracle listener network performance | Options Symptom: (public) Nobody can connect to database Problem: (public) TNS-12518: TNS:listener could not hand off client connection TNS-12547: TNS |
| 160 | Migrate 10g/11g database accross platform | Options Attachment: Migrate Apex Database from Linux to Solaris - Public Version.pdf 166.9 KBytes Symptom: (public) Migrate 10g/11g database accross p |
| 161 | ORA-00600: internal error code, arguments | Options Symptom: (public) ORA-00600: internal error code, arguments: [17059], [0x0C7951CE0], [0x0C7952008], [0x0DE94DEB0], [], [], [], [] when trying  |
| 162 | Oracle 10g RAC Cloning (Real Application Cluster) - Best Practices | Options Symptom: (public) Overview This article will show users two different ways to clone a RAC (Real Application Cluster) database in Oracle E-Busi |
| 158 | Logging in to apps throws Error due to block corruption | Options Symptom: (public) R12 Error: 500 Internal Server Error java.lang.NoClassDefFoundError at oracle.apps.fnd.sso.AppsLoginRedirect.doGet(AppsLogin |
| 642 | Ichor Printer Add or Delete | 1) Login as af user to the concurrent node 2) verify if the printer queue name is already register by following command lpstat -p \|grep OR1POWSSHIPPI |
| 184 | How to turn on debug log for 11i applications server | Options Symptom: (public) Problem: (public) Solution: (public) Subject: How To Collect Apache and Jserv Debugging Details For Applications 11i Doc ID  |
| 163 | RMAN point in time restore | Options Symptom: (public) RMAN restore is successful, but RMAN recovery keeps asking for newer archive logs. Problem: (public) Deleted the requested a |
| 164 | Reconfigre Oracle Enterprise Manager Database Control or DB Console | Options Symptom: (public) Problem: (public) Solution: (public) 1. Change the %ORACLE_HOME%\\network\\admin\\listener.ora file from an IP number to mac |
| 165 | SNMP Monitoring for Oracle Database | Options Symptom: (public) Problem: (public) Solution: (public) Subject: How to start the Enterprise Manager subagent in 10.2.0.4 Doc ID : 737629.1 Typ |
| 166 | Setup standby database for DR | * On Standby database, 1. Copy /ahi/drsync/bin files from another DR server with latest DR sync in place, i.e. cdb1. Update drsync.env. Attachment: DR |
| 167 | Shared Pool Utilization | Options Symptom: (public) Problem: (public) Solution: (public) set echo off spool pool_est /* ******************************************************** |
| 168 | Trace Listener | Options Symptom: (public) Problem: (public) Solution: (public) Tracing Externel Procedures (Extproc) External procedure can be traced using the follow |
| 169 | FRM-91500: Unable to start/complete the build when using frmcmp to compile forms | Options Symptom: (public) FRM-91500: Unable to start/complete the build when using frmcmp to compile forms Problem: (public) Solution: (public) When X |
| 170 | 11i Post Installation Setup | Options Symptom: (public) 11i Post Installation Setup Problem: (public) 11i Post Installation Setup The document covers step to configure A. Forms Ser |
| 171 | 11i Vision Demo Installation issue (RW-50004) | Options Symptom: (public) 11i Vision Demo installation failed at 40% with RW-50004 error. Problem: (public) 11i Vision Demo installation failed at 40% |
| 174 | E-Business R12 show blank page | Options Symptom: (public) You see blank page when accessing E-business suite R12 home page Problem: (public) You see blank page when accessing E-busin |
| 175 | EBS 12.0.6 with Database 10.2.0.4 | Options Attachment: R12 build install-ver4.0.doc 263.5 KBytes Symptom: (public) Problem: (public) Solution: (public) |
| 176 | EBS Failover Primary to Standby | Options Symptom: (public) Problem: (public) Solution: (public) 1. Stop DR sync process by killing syncstdby.job. ps -eaf \| grep syncstdby.job. kill - |
| 177 | EBS R12 forms fails to launch | Options Attachment: oracle installation on rhel 5 prerequisites.txt 3.5 KBytes Symptom: (public) Forms fails to launch and /u01/oracle/lcoa/inst/apps/ |
| 178 | EBS R12 with Database 10.2.0.4 | Options Symptom: (public) Database is not the latest release of 10g Problem: (public) Need to upgrade database for R12 to 10.2.0.4 Solution: (public)  |
| 179 | Enable forms servlet in 11i | Options Symptom: (public) Enable forms servlet in Oracle E-business 11i Problem: (public) Solution: (public) 1. Enable/Disable the Forms Listener Serv |
| 180 | How To Setup Action Export Functionality in Release 11i | Options Symptom: (public) How To Setup Action Export Functionality in Release 11i Problem: (public) Goal ---- The purpose of this note is to summarize |
| 185 | Increase java heap size in R12 to resolve out of memory error | Options Symptom: (public) Not able to start MWA service on R12 due to out of memory java error. Refer to Ticket 2009010510000016. Problem: (public) $  |
| 186 | Increasing Session Timeout in R12 | Options Symptom: (public) Increasing Session Timeout in R12 Problem: (public) Solution: (public) Change the context variable "session_timeout" to the  |
| 187 | Integrating Oracle E-Business Suite Release 12 with Oracle Database Vault 10.2.0.4 | Options Symptom: (public) Need Database Vault Problem: (public) Protect from prvileged users, i.e. DBA's Solution: (public) Subject: Integrating Oracl |
| 188 | Issues with starting listener for new Vision install | Options Symptom: (public) onnecting to (DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=rkg.appshosting.com)(PORT=1531))) TNS-12535: TNS:operation timed out  |
| 189 | Print to HP Deskjet/Inkjet PCL Printer from Oracle EBS | Options Symptom: (public) HP Deskjet printer printing junk from Oracle EBS Problem: (public) HP Deskjet printer prints junk from Oracle EBS Solution:  |
| 190 | R12 500 Internal Error due to end dated GUEST user | Options Symptom: (public) Going to log in page throws 500 Internal Server Error Problem: (public) 500 Internal Server Error oracle.apps.fnd.cache.Cach |
| 494 | update workflow mailer settings from backend | below script allows you to reset the parameters from backend SQL> @$FND_TOP/sql/afsvcpup.sql Component Id Component Name Component Status Type Contain |
| 224 | Creating Microsoft Exchange Distribution Groups | Options Attachment: Exchange Distribution Groups.doc 105.0 KBytes Symptom: (public) Problem: (public) Solution: (public) |
| 209 | Fix gcc issue on dev-app | Options Symptom: (public) Failed to relink INV programs during patching Problem: (public) Failed to relink INV programs during patching. gcc library e |
| 468 | Set up Cisco VPN Client on Mac Using IPSec | Set up Cisco VPN Client on Mac Using IPSec Protocol 1. Click on System Preferences -> Network or the network icon on top right. 2. Click on the + symb |
| 191 | R12 Post Installation Setup | Options Symptom: (public) R12 Post Installation Setup Problem: (public) R12 Post Installation Setup The document covers step to configure 1. Forms Ser |
| 192 | R12 SSO | Options Symptom: (public) R12 SSO Problem: (public) R12 SSO Solution: (public) Bookmark Go to End Subject: Integrating Oracle E-Business Suite Release |
| 193 | R12 log-in error 500 Internal ServerError java.lang.NoClassDefFoundError | Options Symptom: (public) Errors: Browser: 500 Internal ServerError java.lang.NoClassDefFoundError at oracle.apps.fnd.sso.AppsLoginRedirect.AppsSettin |
| 194 | R12.0.6 maintenance patch upgrade | Options Symptom: (public) Need to upgrade to EBS R12.0.6 Problem: (public) Need to upgrade to EBS R12.0.6 Solution: (public) The Oracle E-Business Sui |
| 493 | Workflow settings Nanometrics | Below are settings for Worflow mailer in Nanometrics Production Server Name: outlook.office365.com:993 Manually start components if not already up. |
| 195 | Report Builder:Warning: REP-0004: Unable to open user preference file. | Options Symptom: (public) Report Builder:Warning: REP-0004: Unable to open user preference file. Oracle E-Business Suite R12 Report Builder:Release 10 |
| 196 | Setup IE8 to work with EBS R12 (forms) | Options Symptom: (public) Not able to launch forms in EBS R12 on IE8 an both XP and Vista. Problem: (public) Not able to launch forms in EBS R12 on IE |
| 197 | Start/Stop GRC | Options Symptom: (public) How to start/stop GRC Problem: (public) Solution: (public) Comment: (internal) How to start/stop GRC 1. logon to oraapps.app |
| 248 | Install Agile 9.3 | Options Symptom: (public) Install Agile 9.3 Problem: (public) Install Agile 9.3 Solution: (public) 1. Install Oracle 10.2.0.1 2. Install Oracle 10.2.0 |
| 198 | Start/Stop mwa services on R12 | Options Symptom: (public) Problem: (public) Start/Stop mwa services on R12 e-business environment Solution: (public) http://troubleshootingappsdba.blo |
| 199 | adpatch fails with aijmtabAddTaskTimingColumns: Error running adtasktim.sql, error code (1) | Options Symptom: (public) adpatch fails with aijmtabAddTaskTimingColumns: Error running adtasktim.sql, error code. apps account is locked Problem: (pu |
| 200 | Discoverer service | Options Symptom: (public) Discoverer 10g is down Problem: (public) Discoverer 10g is down Solution: (public) 1. Start 10g infrastructure service $ ssh |
| 201 | Emtek DMZ Configuration | Options Symptom: (public) Emtek DMZ Configuration Problem: (public) Emtek DMZ Configuration Solution: (public) Subject: DMZ Configuration with Oracle  |
| 202 | Emtek DR Failover Procedure | Options Symptom: (public) Problem: (public) Solution: (public) Emtek Disaster Recovery Process Database (See em1:/home/oraprdn/readme.stdby): -------- |
| 203 | Emtek PIX configuration | Options Symptom: (public) Document PIX configuration Problem: (public) Solution: (public) Current Configuration is: : Saved : PIX Version 6.3(5) inter |
| 204 | Emtek Prod DMZ startup and shutdown | Options Symptom: (public) start/stop prod-dmz Problem: (public) start/stop emtek prod-dmz Solution: (public) Startup: (1) make sure Dell agent is not  |
| 205 | Emtek cloning QA | Options Symptom: (public) Problem: (public) Emtek cloning QA Solution: (public) 7. QA testing after cloning 7.1 Test Forms login and query data in for |
| 206 | Emtek cloning procedure | Options Symptom: (public) Clone e-business 11.5.10.2 Problem: (public) Clone e-business 11.5.10.2 from production to refresh non-production environmen |
| 208 | Emtek customer worksheet | Options Attachment: Emtek_new_pass_082009.xls 19.0 KBytes Symptom: (public) Emtek new pass 2009 Problem: (public) Emtek new pass 2009 Solution: (publi |
| 210 | Oracle Email Problem For PO | Options Symptom: (public) Email to Supplier for PO documents has stopped functioning. Problem: (public) Purchasing has reported that the Email to Supp |
| 211 | PROD-DB RAM usage baseline | Options Symptom: (public) Taking baseline of RAM usage on prod-db server Problem: (public) Solution: (public) Server: prod-db With everything down [ro |
| 212 | Oracle Email Problem For PO | Options Symptom: (public) Email to Supplier for PO documents has stopped functioning. Problem: (public) Purchasing has reported that the Email to Supp |
| 213 | Patch Analysis | Options Attachment: Patching.xls 20.5 KBytes Symptom: (public) Patch Analysis Problem: (public) Patch Analysis Solution: (public) Patch Analysis |
| 214 | Server Spreadsheet | Options Attachment: Computers.xls 28.0 KBytes Symptom: (public) Problem: (public) Solution: (public) |
| 215 | Setting From and Reply-To address using MUTT | Options Symptom: (public) Problem: (public) Solution: (public) Update $HOME/.muttrc on all application servers as follows: # Mutt sender profile: # Si |
| 223 | Blackberry Error Adding User "user is in delete pending state on server please recover the user befo | Options Symptom: (public) If user is stuck in pending delete, you can drop user from database using the following: 1. Open command prompt 2. Type "osq |
| 216 | Troubleshoot Emtek backup issue | Options Symptom: (public) RMAN backup fails Problem: (public) RMAN backup fails Solution: (public) Troubleshooting Emtek backup issue 1. Emtek prdn In |
| 217 | Troubleshoot application locking | Options Symptom: (public) Application database lock Problem: (public) Application database lock Solution: (public) FIND BLOCKING LOCK in DATABASE: sel |
| 218 | Workflow mailer settings on production | Options Symptom: (public) Users complain they do not receive PO notification email on production Problem: (public) Workflow notification mailer proces |
| 219 | ipayment and paypal integration | Options Attachment: OracleiPayment.pdf 1.3 MBytes roots.zip 125.4 KBytes Symptom: (public) Config ipayment and paypal integration Problem: (public) Co |
| 220 | How to edit configuration in WebLogic server using WLST | 1. Go to $WL_HOME/common/bin and run wlst.sh 2. Connect to admin server: connect('weblogic','passwd','t3://host:port') 3. easeSyntax 4. cd to where th |
| 221 | Install GRC, GCC, and PCG | Options Symptom: (public) Problem: (public) Solution: (public) PCG 7.3.1 (formerly Logical Apps): URL: http://oraapps.appshosting.com:8001 http://oraa |
| 222 | Add Host/IP to Exchange SPAM Whitelist | Options Attachment: Spam-Whitelist.jpg 244.3 KBytes Symptom: (public) Problem: (public) Solution: (public) |
| 230 | Remotely Reboot a Windows Server (Remote Reboot)\r\n | Options Symptom: (public) shutdown /m \\\\ah59 /r Problem: (public) Solution: (public) |
| 225 | Editing Sharepoint Documents (in non Read-Only Mode)\r\n | Options Symptom: (public) NOTE To edit docs in our sharepoint site (https://remote.appshosting.com:987), you must do the following: (1) Use Internet E |
| 226 | Fix for Exchange users with mailbox but no SBS user account | Options Symptom: (public) User and email exists from Exchange Management Console, but does not show from SBS Console. Problem: (public) Solution: (pub |
| 227 | How to give desktop access through Citrix | Options Symptom: (public) Problem: (public) Solution: (public) I. Give access to desktop from Windows 1. Open Computer Management in Administrative To |
| 228 | Microsoft Exchange - Add New Users | Options Attachment: Exchange Add Users.doc 870.0 KBytes Symptom: (public) Problem: (public) Solution: (public) |
| 229 | Remote Desktop - Network Level Authentication RDP | Options Symptom: (public) You must have installed Windows XP Service Pack Two or above, with the latest RDP Client, then perform the following if you  |
| 328 | Error occurred during initialization of VM\r\nCould not reserve enough space for object heap\r\nCoul | While restarting the tomcat service on the server, If you get following error =============================== Using CATALINA_BASE: /usr/local/jakarta/ |
| 231 | Restore Microsoft Exchange Server from Backup (Windows SBS 2008)\r\n | Options Symptom: (public) We perform full backups of the Exchange server 3 times a day. The restore process is quite painless and takes about 30 minut |
| 565 | Rename Apex Workspace |  |
| 232 | Set up BlackBerry to synchronize email with Exchange | Options Symptom: (public) Problem: (public) Solution: (public) How to set up BlackBerry to synchronize email with Exchange 1. Log into ah57-l using Re |
| 233 | SSM Datasheet | Options Symptom: (public) Problem: (public) Solution: (public) OS: oracle / cle!ora on ah99 orafus / fus!ora on ah103 VNC: :13 on ah103 , :99 on ah99  |
| 234 | Fusion Middleware 11g WebLogic Server 10.3.1 Install | Options Attachment: fusion weblogic 11g 10.3.12 install.zip 4.0 MBytes Symptom: (public) Problem: (public) Solution: (public) Embedded LDAP: 1. From W |
| 249 | Sakura CRP2 migration steps | Options Attachment: CRP2 migration steps and timing.xls 18.5 KBytes Symptom: (public) CRP2 migration Problem: (public) CRP2 migration Solution: (publi |
| 235 | Install OBIEE 10g | Options Attachment: obiee.swf 977.3 KBytes obiee.swf.html 663 Bytes Symptom: (public) Problem: (public) Solution: (public) Start install "setup -conso |
| 329 | Create ACL for APEX mail | REPRODUCE apex_mail ACL issue You can logon to through http://ah128.appshosting.com:7780/pls/apex using following test credantial. Workspace: testmail |
| 330 | ntpdate not uptating time with time servers | echo 1 > /proc/sys/xen/independent_wallclock |
| 236 | Install SOA Suite 11g and Web Center Suite 11g | Options Symptom: (public) Problem: (public) Solution: (public) Repository Creation Utility: processes >= 500 open_cursors >= 300 Use stand-alone WebLo |
| 237 | Installing Fusion Middleware Identity Management Suite | Installing Fusion Middleware Identity Management Suite Options Attachment: WLS Install.doc 134.5 KBytes RCU and New Domain Config.doc 408.5 KBytes IDM |
| 238 | Starting Oracle Fusion on Shared Fusion Server ah71 | Options Symptom: (public) Problem: (public) Solution: (public) 1. Log in as oradb and start fusdb listener and database. 2. Log in as oracle user and  |
| 239 | Bounce OTRS service | Options Symptom: (public) Bounce OTRS services Problem: (public) Solution: (public) Stop OTRS service $ ssh username@otrs-l.appshosting.com $ su - roo |
| 250 | Sakura Data Sheet | Options Attachment: Sakura.pcf 669 Bytes Sakura_data_sheet_New.xls 29.0 KBytes Symptom: (public) Problem: (public) Solution: (public) |
| 240 | Close OTRS ticket from backend | Options Symptom: (public) Can't close some OTRS ticket from front end Problem: (public) When closing certain ticket (sent from support@appshosting.com |
| 401 | Adding printer in Linux | Follow the attached document. Prerequisite: First find out which server runs the concurrent managers. The printer should be set up on that server. For |
| 498 | Set up Google Mail Spam Whitelist | Title: Set up Google Mail Spam Whitelist Applies to: Google Apps E-mail References: Solution: 1. Navigate to Google Apps Admin console. 2. Click Apps. |
| 241 | How to split large files and transfer over slow or unreliable network | Options Symptom: (public) network drops or stalls in middle of file transfer over slow or unreliable network Problem: (public) Solution: (public) 1. G |
| 242 | How to use OTRS | Options Symptom: (public) How can I use OTRS best way? Problem: (public) Need to use OTRS Solution: (public) Use OTRS Comment: (internal) Log-in links |
| 306 | How to install cygwin xwindow tool on PC | This document shows step by step how to install cygwin tool on your PC |
| 243 | ITIL / ITSM - Where do I start ? | Options Attachment: ITIL-ITSM_Where_Do_I_Start.pdf 194.0 KBytes Symptom: (public) ITIL or ITSM Where do I start? What is ITIL? ITIL (Information Techn |
| 244 | Peoplesoft Installation HRMS and CS 9.0 | Options Attachment: Peoplesoft HRMS and CS 9.0 Install Document Part - I.doc 2.8 MBytes Peoplesoft HRMS and CS 9.0 Install Document Part - II.doc 1.8  |
| 255 | Sakura production cut-over steps and timing | Options Attachment: Production migration steps and timing.xls 44.5 KBytes Symptom: (public) Sakura production cut-over steps and timing Problem: (publ |
| 331 | Maujim Upgrade from 8.1.7.4 to 10.2.0.4 | Please find the attached document |
| 332 | BIB-7000 Cannot access X Server error in Notification Mailers | Symptom: Getting error - "BIB-7000 Cannot access X Server" when tried clicking on Notification Mailer from > System Admin -> Workflow Manger -> Throug |
| 245 | Utilities need for working | Options Attachment: AHVPN.pcf 646 Bytes emtek_admin.pcf 745 Bytes Sakura.pcf 669 Bytes Symptom: (public) Problem: (public) New employee to install uti |
| 246 | DSAKUI cloning procedure | Options Symptom: (public) Cloning DSAKUI Problem: (public) Cloning DSAKUI Solution: (public) DSAKUI cloning procedure 1. Backup key files on DSAKUI 2. |
| 247 | Fix AutoVue issue | Options Symptom: (public) when I tried to view an attached file, it gives an error "Unsupported File Format". Problem: (public) Can not view pdf and o |
| 251 | Sakura Platform migration run book | Options Attachment: Sakura development 11.5.10.2 plaftform migration run book.doc 116.5 KBytes Symptom: (public) Problem: (public) Solution: (public) |
| 252 | Sakura VPN pcf file | Options Attachment: Sakura.pcf 669 Bytes Symptom: (public) Sakura VPN pcf file Problem: (public) Sakura VPN pcf file Solution: (public) |
| 253 | Sakura data purging run book | Options Symptom: (public) Purge data Problem: (public) Purge data Solution: (public) 1. Purge workflow 1.1 Query all the Parent and Child Processes fo |
| 254 | Sakura data purging run book | Options Symptom: (public) Purge data Problem: (public) Purge data Solution: (public) 1. Purge workflow 1.1 Query all the Parent and Child Processes fo |
| 256 | Creating a dblink for Taiping for FSG migration | Options Attachment: dblink.rtf 6.1 MBytes Symptom: (public) Create database link for FSG migration Problem: (public) Solution: (public) For creating D |
| 257 | Discoverer 10g Insallation and Setup | Options Attachment: Discoverer 10g Installation and Setup.doc 2.0 MBytes Symptom: (public) Install and Setup discoverer 10g Problem: (public) Install  |
| 258 | EBS 12.0.6 Upgrade 10.2.0.4 Database | Options Attachment: R12 build upgrade-ver4.0.doc 160.0 KBytes Tai Ping Upgrade and Linux Migration-uat1.xls 45.5 KBytes Tai_Ping_Upgrade_and_Linux_Mig |
| 259 | Flexfiled software and documentaion | Options Attachment: FlexField_v3664.zip 6.1 MBytes flexfield_install.txt 14.9 KBytes Symptom: (public) Problem: (public) Solution: (public) Attached z |
| 260 | Increasing maxprocs and maxSessions for Discoverer | Options Symptom: (public) When attempting to connect to a Discoverer Portlet page using Viewer or Plus, the following error is encountered: ERROR - A  |
| 261 | MLS patching | Options Attachment: MLS Patching_pdev.xlsx 11.1 KBytes Symptom: (public) Problem: (public) Solution: (public) |
| 275 | Sales and Marketing Worklist | Options Symptom: (public) Problem: (public) Solution: (public) Comment: (internal) Sales and Marketing Worklist New: 1. David Apex quotation (Ashok to |
| 262 | ORA-01403: no data found in newly created Taiping instances | Options Attachment: Taiping_Datasheet_new.xls 29.0 KBytes tpc instance data sheet.txt 3.3 KBytes Symptom: (public) ORA-01403: no data found Problem: ( |
| 263 | Oracle Alerts Setup | Options Attachment: Oracle Alerts Setup.doc 178.5 KBytes Symptom: (public) Problem: (public) Solution: (public) Oracle Alerts |
| 264 | RMAN backup solution | Options Attachment: Taiping RMAN backup solution.doc 58.5 KBytes Symptom: (public) RMAN backup on production Problem: (public) RMAN backup on producti |
| 265 | Tai Ping Data Sheet | Options Attachment: tpc instance data sheet.txt 3.3 KBytes Tai_Ping_Instances.xls 31.0 KBytes Taiping_Datasheet.xls 28.0 KBytes Symptom: (public) Prob |
| 266 | Tai Ping Hosted Architecture | Options Attachment: Tai Ping Architecture.pdf 82.1 KBytes Symptom: (public) Problem: (public) Solution: (public) |
| 268 | Taiping patching history | Options Attachment: Tai_Ping_Patching_History.xls 21.0 KBytes Symptom: (public) Problem: (public) Solution: (public) |
| 269 | Upgrade EBS from 12.0.6 yo 12.1.1 | Options Symptom: (public) Upgrade EBS from 12.0.6 to 12.1.1 Problem: (public) Upgrade EBS from 12.0.6 to 12.1.1 Solution: (public) 1. Shutdown apps 2. |
| 267 | Taiping clone procedure | Team, Please find below urls for all instance post clone steps . TDEV instance https://docs.google.com/document/d/1DZFZDpQ_zm2FNJ_s2BsVvfFcbOlfk2FgNd0 |
| 270 | Web Discoverer 10g installation steps | Options Attachment: Web_discoverer.doc 56.0 KBytes Symptom: (public) Web Discoverer 10g installation steps Problem: (public) Solution: (public) Please |
| 271 | Character set conversion | Options Symptom: (public) Convert character set on umd daabase Problem: (public) Convert character set on umd daabase Solution: (public) # export icon |
| 272 | Old ICON database and iAS information | Old ICON database and iAS information Options Attachment: ICONS_Database_and_Webserver_Info.doc 27.0 KBytes iconsnet_icons_umd_edu_ora.doc 25.0 KBytes |
| 273 | Old ICON database and iAS information | Old ICON database and iAS information Options Attachment: ICONS_Database_and_Webserver_Info.doc 27.0 KBytes iconsnet_icons_umd_edu_ora.doc 25.0 KBytes |
| 274 | Production migration steps | Options Symptom: (public) Migrate UMD to Linux server on ah66 Problem: (public) Migrate UMD to Linux server on ah66 Solution: (public) Migrate UMD dat |
| 276 | Setting KeepAlives on Tomcat JDBC Connections | Example server.xml: docBase="/usr/local/jakarta/tomcat/server/webapps/manager"> |
| 277 | SOP: Xen Server Backup and Restore Process | Xen Export Process Decide which VM to export. Listing of VMs: # xe vm-list Example: uuid ( RO) : 9240073b-da2f-7959-66a1-c96a072cc1f1 name-label ( RW) |
| 278 | Temporary workaround for accessing apex workspace if cpanel is down | Mr. Haugabrook, Please use the following url as temporary access to your apex management site. The url is: http://ah3.appshosting.com:8004/apex/ Use t |
| 279 | Linux Verizon VZAcess Manager | You don't need VZAM in linux to use MBB. You can direct-dial a connection easily using PPP. The settings are as follows: Phone #: #777 User:yournumber |
| 280 | Reset DRAC Password | racadm config -g cfgUserAdmin -o cfgUserAdminPassword -i 2 "hello123" |
| 281 | Add ramdisk | /sbin/mke2fs -q -m 0 /dev/ram0 mkdir -p /ramdisk /bin/mount /dev/ram0 /ramdisk /bin/chown root /ramdisk /bin/chmod 0750 /ramdisk |
| 282 | Fiber Switch Zone Setup | Attached is a document on how to zone our Brocade fiber switches. |
| 283 | Oracle Fusion Administration Guide | Oracle Fusion Administration Guide |
| 284 | Convert SSL Certificate to PFX (Windows) | openssl pkcs12 -export -out NEW.pfx -inkey ../private/\\*.appshosting.com.key -in \\*.appshosting.com.crt -certfile bundle.crt |
| 352 | New *.appshosting.com SSL Certificate | Attached is the renewed 3 year wildcard SSL certificate for *.appshosting.com This can be used for any application or web server that has the URL *.ap |
| 285 | Generating a CSR (Certificate Signing Request) from CPanel | Log into CPanel Webhost Manager, click on “Generate a SSL Certificate and Signing Request” Provide the values against each columns mentioned -Email Ad |
| 286 | Apache SSL Setup on Windows | C:\\Program Files\\Apache Software Foundation\\Apache2.2\\bin>openssl OpenSSL> '' openssl:Error: '' is an invalid command. Standard commands asn1parse |
| 287 | Adding Custom Directives | Adding Custom Directives to httpd.conf Many users are initially daunted by the new system used for generating and managing Apache's core configuration |
| 288 | Customer Worksheet | Customer worksheet for analyst is enclosed |
| 323 | Fix for FRM server connection error logging into forms in R12 environment | You need to disable "cross site scripting (XSS) filter". Here is the steps: 1. Go to Internet Explorer Options (Tools->Options). 2. Click on Security  |
| 324 | How do customer create cron job through cpanel | How do customer create cron job through cpanel |
| 289 | Adding a virtual host in Apache | ADDING A VIRTUAL HOST in Apache for Fadi, and others using AH1 server Added the following directives in /etc/httpd/conf/vhosts/Vhosts_ispconfig.conf o |
| 290 | Add Swap Space to Redhat Linux Server | To add a swap file: 1.Determine the size of the new swap file and multiple by 1024 to determine the block size. For example, the block size of a 64 MB |
| 291 | Linux LVM Create New Logical Volume - Disk RAID | pvcreate /dev/xvdb pvcreate /dev/xvdc pvcreate /dev/xvde pvcreate /dev/xvdf pvcreate /dev/xvdg vgcreate -s 32M VolGroupBackupData /dev/xvdb /dev/xvdc  |
| 353 | Fusion SOA environment connection information | Problem : Attached the screenshot as attachment MaverickConnection problem.doc Solution: This was resolved by doing the following 2 things: 1. For eac |
| 292 | Creating a local file system on Citrix Xen Server | Check the name of the Volume Group: # vgdisplay VG_XenStorage-0bd23b3a-bf79-3f7d-0d8a-8006da3c17bc Create Logical Volume lvcreate -L 1000000M -n datav |
| 294 | How to Copy Directory Structure in Linux | rsync -av --include='*/' --exclude='*' |
| 295 | How to Copy Directory Structure in Linux | rsync -av --include='*/' --exclude='*' |
| 296 | "SAN-Less" iSCSI configuration for Oracle RAC (target & initiator) | "SAN-Less" iSCSI configuration for Oracle RAC (target & initiator) |
| 297 | Metadata backup | Log into XSConsole on each XenServer Provide the login credentials Choose Backup,Restore and Update Choose Schedule Virtual Machine Metadata Choose Da |
| 298 | compile php, pdo, pdo-oci , oci8 oraapps | http://in3.php.net/manual/en/install.unix.apache2.php ./configure --with-apxs2=/usr/local/apache2/bin/apxs --with-pdo-oci=instantclient,/usr/,11.1 --w |
| 299 | AH7 LVM Problem | Upon reboot, LVM volumes (iSCSI based) are inactive. Fix: vgchange -ay mount -a |
| 300 | Creating a XenServer 5.6 Local Storage Repository | STEP 1 Type : fdisk -l you’ll see the listÂ of allÂ volumes and hard drives, then : Type : pvcreate /dev/sdb sdb is my new volume [root@nas01 by-scsib |
| 301 | iSCSI Persistent Device Names - Linux | Configuring persistent storage in a Red Hat Enterprise Linux 5 environment In an environment where external storage (for example, Fibre Channel or iSC |
| 325 | Start and Stop Air Force Fusion Middleware | US Air Force Fusion Startup and Stop |
| 302 | Oracle RAC iSCSI Persistent Device Names | Setting a raw device in RedHat/CentOS 5 for Oracle RAC Apparently the issue got changed since RedHat 4, which had /etc/sysconfig/rawdevices, /etc/init |
| 303 | customer worksheet | This document contains customer and their environment information. It covers most shared and small customers. Large customers have their own individua |
| 304 | How to kill data pump job | SQL> select * from dba_datapump_jobs; OWNER_NAME JOB_NAME ------------------------------ ------------------------------ OPERATION JOB_MODE ----------- |
| 305 | Staffing Angel Database Migration | Install Linux Enteprise Server 10 (SLES 10 SP3) 64-bit 1) export TSA schema from production expdp tsa directory=DP_DIR dumpfile=tsaprod.dmp schemas=TS |
| 307 | Could not reserve enough space for object heap | While restarting the tomcat service on the server, If you get following error =============================== Using CATALINA_BASE: /usr/local/jakarta/ |
| 308 | Production RAC Configuration for Staffing Angel | Production RAC Configuration for Staffing Angel http://download.oracle.com/docs/cd/E11882_01/install.112/e17214/chklist.htm#sthref72 |
| 309 | Citrix Xen Server - Configure with EMC CX4 iSCSI SAN | Citrix Xen Server - Configure with EMC CX4 iSCSI SAN |
| 326 | We can use DBCA to delete and create new RAC database | http://download.oracle.com/docs/cd/E11882_01/install.112/e17214/dbcacrea.htm#RILIN404 Also read deep into SCAN naming and how it affects listener. htt |
| 327 | upgrading to 11.2.2 patchset | Here is the patch # and doc: Patch 10098816: 11.2.0.2.0 PATCH SET FOR ORACLE DATABASE SERVER http://download.oracle.com/docs/cd/E11882_01/install.112/ |
| 310 | Visual Concepts Environment Information | Visual Concept ============== VPN Configuration: 1. Download and Install "Sonicwall Global VPN Client" /b02/infrastructure/software/SonicWall-VPN-Clie |
| 311 | Citrix XenServer Add Host to Pool | xe pool-join master-address=172.1.0.69 master-username=root master-password= force=true |
| 312 | Install EMC PowerPath software for SAN (Linux) | Log into Linux server: cd /b02/infrastructure/software/emc rpm -i naviagent-6.28.22.0.35-1.noarch.rpm rpm -i EMCpower.LINUX-5.3.1.00.00-111.rhel5.x86_ |
| 313 | Logging into Appshosting Switches | Please see the attached document for instructions on logging into our Ethernet Switches. |
| 314 | OPManager Configuration Backup | Is it possible to take a backup of OpManager data and configurations? Backup ------ To take a backup of the data and configurations in OpManager, *Exe |
| 315 | OEM Grid Control 11gR1 Installation on Linux 5 | This article list out details steps to install OEM GRID control 11gR1 on Appshosting Linux server. Q&A: 1. Q: Error saying not enough space to install |
| 316 | Westar Server and Network Details | Dell R410 DRAC: 192.168.0.130 Windows Server : VPN Server Dell R510 - SAS Drives - westar11 DRAC: 192.168.0.131 Xen Server IP: 192.168.0.134 Linux IP: |
| 318 | AppsHosting DNSMadeEasy DNS Backup | AppsHosting DNSMadeEasy DNS Backup |
| 319 | Sakura DRAC Console info | 10.16.3.205 - ebs03 10.16.3.204 - ebs01 10.16.3.203 - ebs02 10.16.3.207 - backup01 10.16.3.218 - Dell ML6000 10.16.3.222 - ( 10.16.5.31 ) ( VMWare Ser |
| 320 | Reset OPManager Admin Password | Edit "c:\\OpManager\\conf\\securitydbData.xml" Replace OPManager encrypted admin password to "d7963B4t" This resets the password to "admin" |
| 321 | Cisco / CentOS TFTP Configuration | Modify /etc/xinet.d/tftp as follows service tftp { socket_type = dgram protocol = udp wait = yes user = root server = /usr/sbin/in.tftpd server_args = |
| 322 | Configuring SNMP Agents for OPManager | Configuring SNMP Agents Configuring SNMP agent in Windows XP/2000,2003 Configuring SNMP agent in Windows NT Configuring SNMP agent in Linux versions p |
| 333 | Configuring custom ring tones - Blackberry | Set custom notification for calls or messages from a specific contact In the profile list, click Advanced . Press the Menu key. Click New Exception .  |
| 334 | Script to find and update pending requests | Here is the select query ------------------------------ Please replace user_name and concurrent_program_name accordingly. You can find the "concurrent |
| 335 | Linux Printers - Backing up and Copying to another Server | Make yourself at home as root on the old server. Issue the following command: printconf-tui --Xexport > printers.xml Now copy the printers.xml to the  |
| 495 | Logs showing with many systemd messages on starting session of user root | Environment: RHEL 7 Issue: Seeing many log entries below in /var/log/messages Jan 27 10:05:01 af161 systemd: Started Session 444236 of user root. Reso |
| 336 | out of memory and exhausted the OS min free Emtek sb-db server | Are they on 32 bit OS? They ran out of memory and exhausted the OS min free which caused the kernel to through OOM. You can set the minimum free memor |
| 337 | Printer install in Linux if we don't finf drivers in Linux | http://hplipopensource.com/hplip-web/gethplip.html Download the tar ball and compile it. It asks for dependency RPMs. |
| 338 | Configuring Discoverer for Emtek REPT | 2. Set Applications Profile Options for Discoverer using AutoConfig (PLEASE make this changes both on Concurrent Tier and forms Tier) To edit the cont |
| 339 | enable disabled printers | #!/bin/bash #This script is to enable any disabled printers # ENABLE=/usr/bin/cupsenable #Get the list of printers that are disabled LPSTAT=/usr/bin/l |
| 341 | Steps for restarting atcostflights Tomact | 1. ssh to root @atcostflights.com 2. su – acf 3. ps –fu acf 4. kill -9 /usr/local/jdk/bin/java -Djava.util.logging.config….. 5. cd /home/acf/apache-to |
| 342 | webhost servers mysql root password | The following is the mysql root password for both webhost servers. fDkjfFR7^65 |
| 343 | How to install Flash to get into Oracle support site | 1. Go to Flash download web site, and download for Linux. 2. Unzip and untar the file. 3. Copy the flashplayer.so file to the users .mozilla/plugins d |
| 345 | Connecting to SAIC | Step 1 Log in via SSH to itlinux.appshosting.com Create SSH tunnel as follows: Source Port: 5950 Destination Port: saic.appshosting.com:5950 Step 2 Us |
| 346 | Opening database after cloning crashes database with error ORA-00450: background process 'QMNC' did  | Perform Media Recovery with Online Redo: Issue the following statements from SQL. 1.Shutdown immediate. 2.Startup mount 3.SELECT member FROM v$log l,  |
| 347 | How to reset weblogic admin password | Reset weblogic password when you forgot after installation. move out /DefaultAuthenticatorInit.ldift move out /servers/AdminServer/data/ldap/DefaultAu |
| 348 | Create and Remove Load Test Environment Using Backup | Create and Remove Load Test Environment Using Backup of EASE Production Sets up load testing environment using a backup of Production. Since the backu |
| 349 | Weblogic & OID upgrade | Video of Weblogic and OID Upgrade. |
| 350 | Upgrade SOA | Upgrade video of SOA |
| 351 | *.appshosting.com SSL Cert valid from 29-Apr-2011 to 1-May-2014 | SSL Cert |
| 379 | Linux Server Provisioning - Virtual Machine | Linux Server Provisioning - Virtual Machine Import the required golden image from Refer to details on Red Hat Linux (5.x) Provisioning.docx - https:// |
| 354 | How to remove Oracle RAC software 11g R2/12c | http://download.oracle.com/docs/cd/E11882_01/rac.112/e16794/adddelclusterware.htm#CWADD1167 0) Disable the Oracle Clusterware applications and daemons |
| 355 | ah217 tomcat | root 5135 1 0 Jun24 pts/2 00:00:15 /usr/local/jdk/bin/java -Djava.util.logging.config.file=/home/alden/tomcat6/conf/logging.properties -Djava.util.log |
| 356 | How to clone Oracle database home | Prerequisites: Make sure the new Oracle home owner has read/write/execute permission on the oraInventory (given in /etc/oraInst.loc) 11.2 or higher: W |
| 357 | Deleting a node from RAC | 1.Ensure that Grid_home correctly specifies the full directory path for the Oracle Clusterware home on each node, where Grid_home is the location of t |
| 358 | How to add new node to 11gR2 RAC cluster | Prereq: * Set up user equivalence for new node. Adding a Cluster Node on Linux and UNIX Systems This procedure describes how to add a node to your clu |
| 359 | Adding/removing node to load balancer | Steps for adding/removing a node to load balancer for Staffing Angel --------------------------------------------------------------------------------- |
| 360 | Commands to Manage Oracle RAC Environments | Set environment variables: Database (orarac user): For Grid and ASM: . oraenv (+ASM1, 2, 3, etc. depending on server) For database instance: . oraenv  |
| 361 | adding iscsi disk to rhel - By Sagar | CentOS / Red Hat Linux: Install and manage iSCSI Volume Internet SCSI (iSCSI) is a network protocol s that allows you to use of the SCSI protocol over |
| 362 | Oracle Applications (11.5.10.2) Installation on Enterprise Linux 4.5\r\nRHEL prerequisites prereqs | Home Articles Scripts Forums Blog Certification Misc Search About Printer Friendly Oracle 8i \| Oracle 9i \| Oracle 10g \| Oracle 11g \| Miscellaneous |
| 363 | Reconfigure EBS after IP address change (new network) | With new IP address change, ran an in-place clone on database and apps tier. Database: cd $RDBMS_HOME/appsutil/scripts per adcfgclone.pl dbTechStack A |
| 364 | Tai Ping Bounce | Stop: 1. Stop apps 2. Enable maintenance page, https://docs.google.com/a/appshosting.com/document/d/1hPs5_gSZHoJUE8186Qi7V7Oh6LWejD91EKzwROAIDMI/edit  |
| 366 | Installing Fusion Middleware SOA Suite and WebCenter | 1. Install database ( https://ah71.appshosting.com:1158/em ). (sys/system password s0aw3b) -> See db1.jpg & db2.jpg 2. Alter system set processes = 20 |
| 367 | Install Oracle Application Management Pack for Oracle E-Business Suite | OS/User Group Requirement E-Business Suite System nodes file system are added to the EM Agent user group list that monitors the machine on which the n |
| 368 | curl SSL issue | There is issue with curl SSL verification. Here is the error we got curl: (60) SSL certificate problem, verify that the CA cert is OK. Details: error: |
| 369 | Adding new Cisco firewall rule | From Configuration terminal window: osfw(config)# access-list ahdirect permit tcp any host 69.17.99.171 eq 8002 osfw(config)# static (inside,outside)  |
| 370 | Forms issue on 32 bit IE9 after jre ((64 bit)) upgrade on R12 instance | We are able to launch forms on IE9 64 bit on windows7. "C:\\Program Files\\Internet Explorer\\iexplore.exe" - IE9 64 bit is working for forms "C:\\Pro |
| 371 | Ask for running script for TPCM AUG2012 AR month end | SQL> @del_orphans_xla_120.sql Enter value for read_only_mode: N Enter value for enter_ledger_id: 2104 Enter value for start_gl_date: 01/08/2012 Enter  |
| 420 | How to effectively copy and paste from Putty | How to effectively copy and paste from Putty 1. Set up Putty to log all session output by choosing Session-Logging option to "All session output". Rem |
| 374 | adoacorectl.sh was existing with status 204 | Got the following error after applying patches. adoacorectl.sh was existing with status 204 Chedked the following logs ------------------------------- |
| 375 | Purge workflow in TPC PTST environment | SQL> update WF_NOTIFICATIONS set mail_status = 'SENT' where mail_status = 'MAIL'; 308 rows updated. SQL> commit; Commit complete. SQL> exit $ sqlplus  |
| 376 | Installing BAM | Installing BAM |
| 377 | Clearing cache in R12 | Clearing cache in R12 Clearing the “_pages” in R12 creates blank login page issue. Please follow the below steps. Navigate to Functional Administrator |
| 378 | Windows 2008 R2 Server Provisioning - Virtual machine | Provision New Windows 2008 R2 Server - Virtual Machine Right click the Xen Server Select New VM In the NEW VM window, select the Operating system Temp |
| 380 | EBS login page not coming up after running autoconfig. | Bounced apps tier, adoacorectl.sh was exiting with status 204. Ran autoconfig to fix the issue. But the login page was not coming up. Shutdown apps an |
| 381 | adoacorectl.sh failing to start with status 204 | Server.xml file getting updated with a character '>' each time we restart instance This is something very peculiar that suddenly appeared today after  |
| 382 | How to troubleshoot DNS problem | To see a machine's IP address info: http://showip.net/ To see domain registrar and DNS server info: http://whois.net/whois/erdsystems1.com |
| 383 | Resize ERD tablespaces | I made all datafiles autoextendable and set maxsize to 4GB as below. SQL> select * from dba_data_files where tablespace_name='FLOW_324410510359378685' |
| 384 | Purchase SSL Certificate from GoDaddy | Purchase and Install SSL Certificate 1. Log in to CA, www.godaddy.com , as “appshosting” user. 2. Go to SSL account area (All Products -> SSL & Securi |
| 385 | APEX view tablespace utilization | Logon to bps workspace as admin user, then go to Administration tab. Click Monitor Activity-> Workspace Schema Reports-> Schema Tablespace Utilization |
| 506 | Code Migration Request | Subject : Code migration request References : Attached instruction. Description of Actions Taken : Upload File & Executed migration steps. |
| 507 | ORDS Installation | ords_public_user/Welcome123 References: http://www.oracle.com/technetwork/developer-tools/rest-data-services/documentation/index.html oracle.com Oracl |
| 508 | How to Test ORDS | Reference: https://oracle-base.com/articles/misc/oracle-rest-data-services-ords-create-basic-rest-web-services-using-plsql 1. Create a URL mapping to  |
| 422 | Error when import Apex workspace | Issue: FYI, I'm trying to install the attached APEX export from MTCTEST workspace to MTCPROD workspace. I'm getting the error attached in the screen s |
| 386 | Not able to open pages using FancyBox iFrames in Apex | Not able to open pages using FancyBox iFrames in Apex ----------------------------------------------------------------------------- Make a change in t |
| 387 | Bounce Apache - EBS | Bounce Apache. ) login to the instance 2) cd $ADMIN_SCRIPTS_HOME 3) adapcctl.sh stop 4) adoacorectl.sh stop Wait for 1 – 2 minutes Check if they are d |
| 388 | Maintenance page for weekend down time of Taiping production system | Enabled maintenance page for 1 hr downtime and edited index.html file. Please use the following procedure to enable maintenance page: (1) Log into pro |
| 389 | OEM,12c startup/shutdown | Startup/Shutdown --------------------- Use the following commands to start all components export ORACLE_HOME=/u01/app/oracle/product/11.2.0/db1 export |
| 390 | Open Manage Installation | Dell OpenManager IT Assistant (dia.appshosting.com) Action -> Include Ranges (spcify IP for each Xen server) Mount ISO image df -TH mount -o loop .iso |
| 529 | How to Add ASM Disk to Disk Group | Prerequisite: Provision ASM disk to server. Source the grid environment and go to grid_home/bin location and trigger asmca in vnc server once triggere |
| 399 | SFTP users creation for boaz | create a user with complete home directory path. useradd sqc_hexcel_sbox -d /home/sftp-users/hexcel/sqc_hexcel_sbox cd /home/sftp-users/hexcel mkdir s |
| 391 | Taiping clone from Standby (DR) | 1. Make sure all the latest archives are applied to VDR1. 2. Pause the sync by running pause.sh. Shutdown standby on VDR1 or tp-dr01 based on the inst |
| 392 | Wiki restore wiki.appshosting.com | Database should be important. No need of webserver files. Backup the database. download the latest mediawiki from the mediawiki site. Then follow the  |
| 509 | Password Less Authentication | https://docs.google.com/document/d/1qAlV4gsXsOk2JDnTRjo4rrX5DkWK5buOrZPvy2uB2p4/edit# |
| 394 | Steps to create Oracle directory on ah3 and mount to prod.appshosting.com | Steps to create Oracle directory on ah3 and mount to prod.appshosting.com ======================================================== Created Oracle Dire |
| 395 | How to drop and recreate TEMP Tablespace in Oracle 9i/10g/11g | How to drop and recreate TEMP Tablespace in Oracle 9i/10g/11g How to drop and recreate TEMP Tablespace in Oracle 9i/10g/11g 1. Create Temporary Tables |
| 396 | HOW TO PURGE E-MAIL NOTIFICATIONS FROM THE WORKFLOW QUEUE | HOW TO PURGE E-MAIL NOTIFICATIONS FROM THE WORKFLOW QUEUE The below outlines the steps. 1) You need to update the notifications you do not want sent,  |
| 397 | Re: FRM - 92095: jinitiator version | If user was able to use forms before and can't do so now, it most likely due to java auto update. User should turn off all auto-update feature on the  |
| 398 | Forward vonage phone to any phone including mobile | Logon to Vonage.com as your account. Go to Features ->Call Forwarding. You can forward vonage phone to any phone including mobile in the world. I set  |
| 400 | what's taking up space and deleting old files in Log/out directory | $ du -ks * (command to first determine what's taking up all space ) =============================================== $ ls -l \|more ( to check the list |
| 510 | Demantra 12.2 installation and setup | Demantra Install on ASCP Requirments The minimum requirements for client machines for all Demantra products is: · 1 CPU at 1.3 GHz or faster · 512 MB  |
| 402 | Taiping SSL | 1. Backup exixting wallet and shutdown apps tier 2. Create a "new_xxxx" directory under SSL directory in applprd home directory 3. Create a new wallet |
| 403 | Restart Hung OPManager | The Opmanager application is hanged. I restarted it. Follow this . 1. Login to AH35 2. Go to Administrative Tools -> Services Try to stop Manage Engin |
| 404 | How to decommission customer environment | When you receive request from customer to terminate their hosting environment, please follow these steps: 1. Support team acknowledges customer reques |
| 405 | Helpdesk Process | Team, As discussed in team meeting, here is the recap of the Soft Close process which will help us keep the open requests count down and make customer |
| 406 | Restore missing archive logs and fix DR sync | 1. Restore missing archives from backup. RMAN> run { 2> allocate channel d1 type disk; 3> restore archivelog from sequence 105057 until sequence 10505 |
| 539 | ORDS - Enable rest services on schema/table | Connect to SqlDeveloper with the requested schema : i.e STAFF right-click on connection name Enable rest on Table: i.e employee: right-click on table  |
| 412 | Upgrade from R12.1.1 to R12.1.3 | Oracle E-Business Suite Release 12.1.3 Readme [ID 1080973.1] To apply Oracle E-Business Suite Release 12.1.3, follow these steps: 1. Use AutoPatch to  |
| 414 | Install Oracle E-Business Suite 12.1.1 | Install Oracle E-Business Suite 12.1.1 |
| 415 | Install ASCP 12.1.1 | Install ASCP 12.1.1 |
| 416 | Oracle Database 11.2.0.3 Upgrade | Install Oracle Database 11.2.0.3 and Upgrade |
| 417 | GRC 8.6.4.5000 installation | Install GRC 8.6.4.5000 for Nanometrics. |
| 407 | Apex 4.2 Installation | @apxrtins.sql SYSAUX SYSAUX TEMP /i/ change the password for the ADMIN account @apxchpwd.sql Unlocking the APEX_PUBLIC_USER Account ALTER USER APEX_PU |
| 421 | Error when import Apex workspace | Issue: FYI, I'm trying to install the attached APEX export from MTCTEST workspace to MTCPROD workspace. I'm getting the error attached in the screen s |
| 511 | Demantra installation on ASCP | Demantra Install on ASCP Overview See “Demantra Architecture.docx” for the architecture Demantra 7.3.1.5 will be install on ascp instances VCP 12.1.3. |
| 512 | Nanometrics RAC installation | Nanometrics RAC installation NANOMETRICS RAC PRE-PROD MIGRATION SYSADMIN tasks: Taking backup of existing Operating system Deleting Existing environme |
| 408 | User do not receive email notification in Password reset Link | Changed profile Local Login Mask to 40 from 32 in CRP and bounced Apache Explanation in SR 3-6419330481 is as below , In order to enable this function |
| 409 | Troubleshoot EBS oacore not starting issue | 1. Bounce EBS application. Move to step 2 if this does not resolve issue. 2. Bounce EBS database. Move to step 3 if this does not resolve issue. 3. Ch |
| 410 | How to check Oracle EBS application profile value | select user_profile_option_name, fnd_profile_options_vl.profile_option_id, level_id , decode(level_id,10001,'Site',10002,'Application',10003,'Responsi |
| 411 | Configure Inbound workflow mailer for TPC | Added 'PROCESS' folder in oracle@taipingcarpets.com mail account. This resolved final issue to start workflow notification inbound email. Here is the  |
| 419 | How to set up apps FTP account for Tai Ping | How to set up apps FTP account for Tai Ping NOTE: This document shows giving "oraftp" user write access to $JAVA_TOP and $TPC_TOP. If other directory  |
| 513 | Ryman 12.2 upgrade Details/installation/configuration | Ryman 12.2 upgrade Details/installation/configuration |
| 514 | Ryman APEX_EBS Integration | Ryman APEX_EBS Integration APEX EBS Integration 1. Add Additional Schema to APEX Workspace 2. Change schema access in APEX application 3. Create Custo |
| 515 | Ryman 12c DB upgrade details/installation/configuration | Ryman 12c DB upgrade details/installation/configuration 1 Terminate patching cycle [apptest@rhtstebsapp2 scripts]$ adop phase=cutover,cleanup Enter th |
| 423 | Clone EBS using rman duplicate for Martek Power | [oradev@oradevdb 12072012]$ sqlplus /nolog SQL*Plus: Release 11.1.0.7.0 - Production on Sat Dec 8 16:28:29 2012 Copyright (c) 1982, 2008, Oracle. All  |
| 424 | ADPATCH automation | Using Defaults file with adpatch (Non Interactive) How to create defaults file ? adpatch defaultsfile=$APPL_TOP/admin/$TWO_TASK/defaults.txt Now abort |
| 425 | OAFM fails to start | OAFM OC4J is Not Starting. Error: "time out while waiting for a managed process to start" [ID 952583.1] To Bottom Modified: Jul 12, 2012 Type: PROBLEM |
| 426 | Start/stop GRC services | GRC startup/shutdown procedure 1 Startup 1.1 Start GRC database [oradev@c1devgrc1 scripts]$ sqlplus /nolog SQL*Plus: Release 11.2.0.3.0 Production on  |
| 427 | Request View Output and Log throws File Server Error in EBS | Drill down to following setup to resolve this issue. CONCURRENT: Report Access Level => responsibility RRA: Service Prefix => leave as blank RRA: Enab |
| 428 | Change Discoverer Timeout | How To Change The Session Timeout For Discoverer 10g(10.1.2) Or Discoverer 11g [ID 567588.1] To Bottom Modified: Nov 30, 2011 Type: HOWTO Status: PUBL |
| 429 | Install OID run book | https://hr1.taipingcarpets.com:4452/OA_HTML/AppsLocalLogin.jsp Larry: ias_admin user: orcladmin ias_admin password: admin1 Larry: URL: http://hr1.taip |
| 430 | E-Business Suite Browser Settings for Internet Explorer | Internet Explorer for E-Business Suite The settings below should work with the latest versions of IE – so far IE8; IE9 and IE10. Version specific sett |
| 413 | Schedule360 Database cloning procedure | (Refer to customer worksheet for Staffing Angel credentials) Purpose of this document is to clone Staffing Angel production RAC database to refresh no |
| 431 | Cpanel Directory Add | ssh into prod.appshosting.com from putty what we need to do is have our own ID. =============================== login as: sam sam@prod.appshosting.com |
| 445 | How to copy PLPDF license from one database to another on same server | PL/PDF allows the same license to be used on multiple databases on the same server. Prerequisites: Install PL/PDF software (download PL/PDF SDK from w |
| 516 | UMD RAC install/configuration | UMD RAC install/configuration [orarac@af86 ~]$ cd /b01/12102_grid/grid/ [orarac@af86 grid]$ ./runcluvfy.sh stage -pre crsinst -n af86,af87 -verbose Pe |
| 517 | RAC SCAN Listener - Setup & Troubleshooting | RAC SCAN Listener - Setup & Troubleshooting Reference: Grid Infrastructure Single Client Access Name (SCAN) Explained (Doc ID 887522.1) 1. Introductio |
| 566 | How to drop Mariadb database and user and recreating database and user | [root@af281 ~]# mysql -u root -p Enter password: Welcome to the MariaDB monitor. Commands end with ; or \\g. Your MariaDB connection id is 172 Server  |
| 432 | Resize Linux File System On Line | Requires the File System be mounted on Logical Volume. 1. Extend LV [root@prod ~]# lvextend -L +50G /dev/VolGroup_01/LogVol-u01 Found duplicate PV NlZ |
| 433 | Troubleshoot OC4J issue in Oracle 10.1.4 application server | [orassot@hr1 chgip]$ cd config [orassot@hr1 config]$ ls chgip.log.properties domainame.lst hostname_short_sample.lst.xml chgip.properties hostname.lst |
| 434 | Mac OSX Installation of Cisco VPN Client Throws Damaged File Error | Problem: Mac OSX Installation of Cisco VPN Client Throws Damaged File Error Solution: Need to allow 1. Navigate to: System Settings icon (looks like c |
| 486 | Tai Ping - Odyssey Jitterbit and Concur Atom Boomi | TDEV2: SQL> create user boomi identified by ora123 default tablespace sysaux temporary tablespace temp; User created. SQL> grant create session to boo |
| 435 | In 12.1.X version of Applications when I set the over ride address (test address for the mailer ) I  | There is a new parameter for the mailer in 12.1.X version of the mailer called Outbound User. It is on screen 3 of 8 of the mailer configuration and u |
| 436 | Log in to R12 Forms | Log in to R12 Forms First Time Log in to application and choose responsibility and menu that launches Forms. When it asks to install new Java, grant p |
| 454 | Nanometrics : Quoting :Place Order : ORA-12541 : TNS No Listener | Problem : Quoting :Place Order : ORA-12541 : TNS No Listener Solution : --> Start vertex tomcat : [oravertex@c1pchebsapp1 VERTEX]$ cd tomcat/bin/ [ora |
| 437 | Updated Office 365 after Upgrade on May 25, 2013 | Added CNAME autodiscover for autodiscover.outlook.com MX record appshosting-com.mail.eo.outlook.com. TXT record with VALUE v=spf1 include:spf.protecti |
| 438 | Set up Cisco VPN Client | How to Set up Cisco VPN Client Download and install standard Cisco IPSec VPN client : www.appshosting.com/vpn/ vpnclient .exe (32 bit Windows) www.app |
| 533 | RMAN-06004: ORACLE error from recovery catalog database: RMAN-20001: target database not found in re | [orabfs05@af261 bin]$ rman target sys/Bf$sys129 catalog rcat/oracle123rcat@RMANCATALOG Recovery Manager: Release 12.1.0.2.0 - Production on Mon Sep 25 |
| 439 | Install Fusion Application 11.1.1.6 | See attached detail step by step instruction to install Fusion Application 11.1.1.6 in two tier configuraiton. Node 1 includes Oracle Identity and Acc |
| 440 | Upgrade GRC 8.6.4.500 to 8.6.4.700 | See attached detail logs for GRC 8.6.4.700 upgrading steps. |
| 441 | GRC performance tuning | User: Larry Zhao Private 04/07/2013 01:11 AM case "${SERVER_NAME}" in "AdminServer") USER_MEM_ARGS="-Xms4g -Xmx8g" ;; "AdminServer(admin)") USER_MEM_A |
| 442 | Discoverer 11g installation | Discoverer 11g installation |
| 443 | Staffing Angel production maintenance | User: Larry Zhao Private 06/07/2013 01:42 AM # Disable restricted mode to performance maintenance SQL> alter system disable restricted session; System |
| 444 | Troubleshoot GL Wand performance issue in R12.1.3 | From: Meggie M. Shi Sent: Thursday, July 11, 2013 7:00 PM To: Shashin Bhavsar Cc: Sam Yun; Alan Porto; Support Group Subject: Re: Weekly Meeting to Di |
| 446 | Creating and Assigning New Schemas to Existing Workspace | 1. Create tablespace for data and index: create tablespace abc_data datafile '/u01/oradata/ahdb07/abc_data01.dbf' size 100m autoextend on maxsize 300m |
| 447 | How to check APPS password and SYSADMIN password | 1. Create decrypt function. SQL> create FUNCTION apps.decrypt_pin_func(in_chr_key IN VARCHAR2,in_chr_encrypted_pin IN VARCHAR2) RETURN VARCHAR2 AS LAN |
| 627 | Schedule 360: Apex URL login Page shows 500 Internal Server Error | Problem: Below URL’s are not coming up. Error: 500 Internal Server Error So we troubleshoot as per below details: http://af357.appsforte.com:8080/ords |
| 448 | Resolve Java based programs very slow or not starting after server reboot | Problem: Java based programs either are very slow or not starting. For example, Java based concurrent requests ( PO Output for Communication Format Pa |
| 449 | Oracle SSO 11g Installation | Oracle SSO 11g Installation : Attached. High Level task doc : https://docs.google.com/a/appshosting.com/spreadsheet/ccc?key=0AvSvMxmV9BisdEhCSWR1S1E5b |
| 450 | Setting up nVision for PeopleSoft | Installing and Configuring nVision for APA PeopleSoft For nVision: · I think you will need to have Excel installed on Citrix. · Save the attached file |
| 451 | Nanometrics ASCP document | Nanometrics ASCP document |
| 452 | Nanometrics Vertex Installation | IMPORTANT : Please make sure to always start Vertex services among Apps Services to avoid quote error ( Solution #454) Instructions in AH Vertex Insta |
| 453 | Creating APPS READ ONLY schema in Oracle Applications | step 1: Connect as sysdba and create the database user to be used for apps read only schema. bash $ sqlplus "/ as sysdba" SQL > create user appsro ide |
| 455 | Windows command utility to get all folder sizes - du | Introduction Du (disk usage) reports the disk space usage for the directory you specify. By default it recurses directories to show the total size of  |
| 639 | Unlock APEX Internal Admin Account | 1) Find the security group ID of internal workspace: SQL> select apex_util.find_security_group_id (p_workspace => 'INTERNAL') from dual; APEX_UTIL.FIN |
| 572 | Nanometrics Clone -- New --> MOVED to Below Doc | Moved to Onto Nanometrics Clone, https://docs.google.com/document/d/1OPz4H4ihcJ3UEVAgwmHI5Fu6npxtiolzhLfz-jtGfAM/edit 1. Run adpreclone.pl on producti |
| 456 | Create read only apps account | http://oracle.anilpassi.com/read-only-schema-in-oracle-apps-11i-2.html SQL> create user apps_query identified by ah229query default tablespace APPS_TS |
| 457 | Start MWA service using adstrtal.sh | Mobile Web Applications Server - How to Start/Stop MWA Services Using Control Scripts adstrtal.sh/adstpall.sh Oracle Mobile Application Server - Versi |
| 458 | Performance tuning for IMPEBS | Below are the Fixes incorporated in IMPEBS. [oraimp@c1pchebsdb1 dbs]$ diff initIMPEBS.ora initIMPEBS.ora.bkp 219,220c219,220 --- > processes = 200 # M |
| 459 | Avalara - fixes | - Installation guide - Post-steps and common errors. |
| 461 | Nanometrics : Some users get the error APP-FND-01630 after clone | After clone nanometrics instances some users might get the error APP-FND-01630 while opening forms. Check the profile ICX: Forms Launcher at user leve |
| 473 | Run RDA in EBS | Remote Diagnostic Agent (RDA) - Getting Started (Doc ID 314422.1) |
| 490 | Apply EBS 12.2.x Patches | Online (default): 1. Extract patch in $PATCH_TOP 2. adop phase=prepare adop phase=apply patches=23513973 adop phase=finalize adop phase=cutover adop p |
| 462 | Harden Database Password Policy | 0. Create VERIFY_FUNCTION using script in rhtstebsdb2:/home/oratest/verify_func.sql. 1. Set up new security profile create profile SEC_BUSINESS_USER l |
| 463 | Setting up Application Express for HTTP Server | Set up Oracle HTTP Server for APEX http://docs.oracle.com/cd/E37097_01/doc/install.42/e35123/otn_install.htm#BHAFJJDA 3.5.5.4 Configuring Oracle HTTP  |
| 469 | How to purge e-mail notifications from the Workflow queue | How to purge e-mail notifications from the Workflow queue Some times Due to large number of e-mail notifications to accumulated in the queue Workflow  |
| 470 | Change Apex Internal ADMIN password | 1. Log in to the Apex database as SYS user. 2. Run either apxxepwd.sql or apxchpwd.sql and pass in complex password, for example T3mp!234. If you don' |
| 471 | ORA-00600 [17148] - Apex Upgrade | SR 3-8710723831 Instead of using : HTMLDB_UTIL.set_session_state ('P149_DEBUG', l_ddl); HTMLDB_UTIL.set_session_state ('P149_DEBUG',v ('P149_DEBUG') \ |
| 472 | How to Log in to Helpdesk User Portal | 1. Navigate your browser to https://helpdesk.appshosting.com. 2. Log in with your Helpdesk user name and password. If you created a request by sending |
| 474 | Enable RESTful Web Services in APEX | Enable RESTful Web Services in APEX Use the Allow RESTful Accessattribute to control whether developers can expose report regions as RESTful services. |
| 542 | Import SSL Certificate into wallet . | 1. Get the certificates from the browser : ie stripe: Transfer certs to the server Import then into wallet : [oraapex@af3 certs]$ orapki wallet add -w |
| 475 | Query to get Scheduled requests in EBS | select r.request_id, p.user_concurrent_program_name \|\| nvl2(r.description,' ('\|\|r.description\|\|')',null) Conc_prog, s.user_name REQUESTOR, to_ch |
| 476 | Rebuild Mailer's Queue when it is Inconsistent or Corrupted | How to Rebuild Mailer's Queue when it is Inconsistent or Corrupted? (Doc ID 736898.1) Sometimes the queues get stuck.. we find that rebuilding the que |
| 477 | Install Apex REST data service | Setup REST data service for Apex http://docs.oracle.com/cd/E37099_01/doc/doc.20/e25066/install.htm#AELIG7019 1.2.2 System Requirements Oracle REST Dat |
| 478 | Avalara Health Check | We can use two methos to verify the connection to Avalara Site. #1 - From DB : Login to db as avalara user ( password in customer datasheet) and run t |
| 479 | Error installing OEM Agent due to improper deinstall of existing agent software | [oracle@ah222 bin]$ ./emcli delete_target -name="rs05.appshosting.com:1830" -type="oracle_emd" -delete_monitored_targets -async Target "rs05.appshosti |
| 480 | DR Test | Here are the 2 options for doing DR Test, one limited DR test and the other more thorough DR test: Option 1 - Limited DR testing: 1. Pause DR sync pro |
| 482 | How to resize ASM Disk Groups with new ASM disks | How to resize ASM disk groups with new ASM disks Prerequisite: ASM disks must be first presented to the cluster servers. Part 1: Add new ASM disks. 1. |
| 483 | UMD ICONS RAC | Please take a full backup of icondata database on ah66 once the down time starts after we bring down the application . Do a log switch alter system sw |
| 484 | Jitterbit Installation | Jitterbit Installation 1. On vprod1 server, install Jitterbit agent as root user to /opt/jitterbit directory: rpm -ivh jitterbit-agent-7.2.2- 1.i386.r |
| 485 | INS-40406 The installer detects no existing Oracle Grid Infrastructure software on the system | This error could happen, if you have an incorrect Inventory on your server. To fix this problem, you can start (from the old GI Home) the Oracle Unive |
| 487 | How to fix DR sync error due to missing data file | How to fix DR sync error due to missing data file [oraprd@tp-dr01 bin]$ tail applylog.log ORA-00280: change 12205265334572 for thread 1 is in sequence |
| 488 | Change WF Mailer test address | From backend : select fscpv.parameter_value from fnd_svc_comp_params_tl fscpt ,fnd_svc_comp_param_vals fscpv where fscpt.display_name = 'Test Address' |
| 489 | Images Missing on static workspace location | Images Issue: Images are not copied from static files location for the application to the new server: Solution: 1) Login into the application and shar |
| 496 | Oracle Database ACL | Reference: http://dbtricks.com/?p=159 1. Create an ACL (if not already created) 2. Add privileges to the USER that will use the network resources. 3.  |
| 497 | How to Restart Tomcat 8 | 1. Go to Tomcat bin. i.e. /home/tomcat8/apache-tomcat-8.5.8/bin 2. Issue shutdown. i.e. ./shutdown.sh 3. Issue startup. i.e. ./startup.sh |
| 643 | Taitron Front End Access | Workaround URL to launch form - Taitron Oracle Application 11i 1. Access the URL to launch the form 2. Install JInitiator if needed. (We have performe |
| 499 | How to Decrypt files | 1. Goto file location as a root user. 2.Use this command to Decrypt the files "gpg --yes --batch --passphrase=B5SxM8R4SwLp9N filename " |
| 500 | adclgclone.pl fails with error Perl lib version (5.10.1) doesn't match executable version (v5.10.0) | Reference: Adding External Node via perl adcfgclone.pl appsTier Error: Perl lib version (5.10.1) doesn't match executable version (v5.10.0) (Doc ID 16 |
| 501 | Db Objects Re-compilation | 1. enable java : exec dbms_java_dev.enable ; 2. set _disable_fast_validate=TRUE in init.ora file and restart the DB. 3. Invalidate all the objects - u |
| 502 | Fix Timestamp Mismatch Issue in EBS database | 1. Verify if you have indeed timestamp mismatches (see queries provided under the cause). 2. Check any invalid objects you have. Run: select object_na |
| 504 | Restart 12.1.3 Apps Services | Problem : Unable to log in to apps References : Symptoms : Able to log in to database, but not apps Causes : Apps services throwing error in log Solut |
| 505 | File Upload Request | Subject : Upload F¡le to Server request References : Description of Actions Taken : File Has been uploaded to requested server. |
| 518 | HowtoVerifyVertexisworkingfromEBSApplication | HowtoVerifyVertexisworkingfromEBSApplication How to Verify Vertex is working from EBS Application 1. Log in to EBS with US AR Super User responsibilit |
| 520 | Brightfield performance issues/troubleshooting | Brightfield performance issues/troubleshooting UAT PRD BFS>alter session set sql_trace=true ; Session altered. Elapsed: 00:00:00.05 BFS> execute pkg_u |
| 521 | IE 11 Prerequisite Patch Requirements for R12 | IE 11 Prerequisite Patch Requirements (Required) Reference: R12: Recommended Browsers for Oracle E-Business Suite (Doc ID 389422.1 ) To use IE11 with  |
| 522 | Timestamp mismatch fix | Timestamp mismatch fix Stop apps services: Enable JavaDev SQL> exec DBMS_JAVA_DEV.ENABLE; PL/SQL procedure successfully completed. SQL> Check Invalids |
| 604 | ICHOR space alerts and clearing | when we get alerts from ichor with respect space generally under log directory in /u06 mount point we go the following directory /u06 (/dev/vg04/lvol7 |
| 603 | Database DR Lag Issue | From time to time, we receive alerts for database being out of sync. 99% of the times, disk space on DR server where we store archive logs is exhauste |
| 523 | Useful Oracle & Linux commands - Diego | Check linux processes waiting for io resources : [root@c1prdebsapp1 ~]# for x in `seq 1 1 100`; do ps -eo state,pid,cmd \| grep "^D"; echo "----"; dat |
| 524 | Enhanced Jar Signing for Oracle E-Business Suite (Doc ID 1591073.1) | Section 2: Prerequisite Requirements Step 2.1. Enhanced Jar Signing Prerequisite Requirements Oracle Applications 11.5.10 CU2 or higher or Oracle Appl |
| 525 | Useful SQL APPS scripts - Trinadh | Query to get incompatible programs http://www.oracleebs.net/2011/07/query-to-get-incompatible-programs.html SELECT fat.APPLICATION_NAME, fctl.user_con |
| 526 | Workflow Mailer - SSL/Certificates | Introduction : If SSL connection to imap is enabled, the java keystore needs to be configured and email server certificates need to be imported. Check |
| 527 | Create service based monitoring using OEM | How to create service based monitoring using OEM Navigate to Home page à Targets à Services - à create service |
| 640 | Load program running long time in Production | Subject : PO Accrual Reconciliation load running very slow, over 9 hours References : Symptoms (if applicable) : Causes (if applicable) : Full table s |
| 530 | How to Put Concurrent Requests On Hold and Release Hold | Reference: http://erpondb.blogspot.com/2016/01/place-all-pending-concurrent-requests.html Procedure: 1) Create a backup table containing the pending r |
| 531 | Brightfield Database Clone-Refresh | Prerequisites: 1. Clean up the old data files to be refreshed. 2. Ensure you have a good backup of source database. 3. Clean up rman catalog of the da |
| 534 | Demantra Performance Tuning - REORG of Demantra tables | BEGIN table_reorg.reorg('DEMANTRA_3','AUDIT_TRAIL','R'); END; / BEGIN table_reorg.reorg('DEMANTRA_3','MDP_MATRIX','R'); END; https://support.oracle.co |
| 535 | Not Use - Demantra 12.2.6.2 refresh - NOT USE | expdp demantra_3/demantra_3 schemas=DEMANTRA_3 directory=DATA_PUMP_DIR dumpfile=DEMANTRA_3.dmp logfile=expdpdemantra_3.log impdp demantra_2/demantra_2 |
| 536 | How to Change Windows Password | 1. Log in to Windows server as administrator (or administrator1 on Windows workstation). 2. Open Control Panel. 3. Click on User Accounts, Change acco |
| 540 | Demantra R12 Schema refresh | -- Make sure to close Modeler from RDP once that post-clone refresh is completed -- If demantra_2 schema is part of complete db clone , make sure to u |
| 611 | Queries for Journal Entries | **NOTE: Please ensure that your SQL Developer is 20.2 or above version to export the records more than 30K. GL Journal Query : Select jeh.JE_HEADER_ID |
| 541 | Creating a directory in database | [oradev@c1pdevascp1 PRJASCP]$ mkdir KKOCH [oradev@c1pdevascp1 PRJASCP]$ chmod -R 777 KKOCH [oradev@c1pdevascp1 PRJASCP]$ mkdir KKOCH_ARCHIVE [oradev@c |
| 183 | How to find EBS patch info | Options Symptom: (public) Need to find out the latest patchset level and patch info Problem: (public) Need to find patch set level and patch info Solu |
| 543 | EBS patching for 11.5.10.2 and 12.1.X | Reference : http://dbakevlar.com/2012/11/how-to-apply-an-ebs-patch/ EBS Patching: 1. Download the patch, as instructed by Oracle Support and also read |
| 545 | Oracle Database patching | Reference: http://rafioracledba.blogspot.in/2011/05/how-to-apply-database-patches.html Database patches are of various kinds like,But in broad there a |
| 583 | Concurrent output ftp delivery. | 1. Create ftp user for testing: [root@c1tstebsapp1 ~]# useradd ftpconc [root@c1tstebsapp1 ~]# passwd ftpconc [root@c1tstebsapp1 ~]# su - ftpconc [ftpc |
| 546 | EBS forms/iAS patching | Reference : http://onlineappsdba.com/index.php/2006/10/01/forms-ias-and-other-component-patching-in-oracle-apps/ Forms Patches : These patches update  |
| 547 | Setting up override email address for WF mailer | Steps for setting override email address for WF mailer is in screenshots attached . |
| 548 | Bounce weblogic on Demantra 12.2.6.2 | login as oracle user then execute below , [oracle@c1prjdem03 ~]$ /home/oracle/bin/stop_weblogic.sh Once the script comes out , execute start script as |
| 549 | Bouncing EBS application and database(RAC) | [appstg@c1stgebsapp1 scripts]$ adstpall.sh apps/apps [appstg@c1stgebsapp2 scripts]$ adstpall.sh apps/apps Check for any processes and wait for 5 mins  |
| 550 | Creating Application User ID in EBS | Login to EBS instance as sysadmin/ --->click on system administrator responsibility--->Security--->User-->Define This will open up User define form an |
| 559 | Workaround when Archive Area Gets Full | 1. Go to the archive directory which filled up. cd $ORACLE_HOME/dbs/arch 2. Move files to another file system that has space available. mv 1_513*.dbf  |
| 558 | Apex Upgrade from 5.0 to the current release 5.1.4 | [oravis@af9 ~]$ cd /u01/oracle/stage [oravis@af9 stage]$ ls -ltr total 89980 -rw-r--r-- 1 oravis dba 92038978 Jan 12 18:06 apex_5.1.4_en.zip drwxrwxr- |
| 638 | Mail deliver issue | Title : Unable to send e-mail from GRACE_AF schema References : Symptoms (if applicable) : Causes (if applicable) : ACL not allowing Procedure or Solu |
| 552 | Steps to resolve ACL error with Demantra schema | [orastg@c1stgascp1 admin]$ sqlplus /nolog SQL*Plus: Release 11.2.0.4.0 Production on Fri Oct 13 00:47:51 2017 Copyright (c) 1982, 2013, Oracle. All ri |
| 553 | Java Upgrade(Configurator issue) | Apply ebs patch 21624242 Link pre-req [root@c1devebsapp1 lib]# mv libXtst.so.6 libXtst.so.6.ori [root@c1devebsapp1 lib]# ln -s /usr/X11R6/lib/libXtst. |
| 554 | Modifying Spfile and bounce the RAC database: | Modifying Spfile and bounce the database: METHOD 1 : Simply issue the following SQL statement from any of the nodes ALTER SYSTEM SET = scope=spfile; T |
| 556 | Not able to login business modeler FIX | Login to Db and kill modeler.exe session: SQL> select sid,serial#,program,module,machine,osuser from v$session where username='DEMANTRA_2'; SID SERIAL |
| 641 | Convert .PFX Certificate to Base 64 cert and key | Title : Convert .PFX certificate to Base64 cert and key References : Symptoms (if applicable) : Causes (if applicable) : Procedure or Solution Descrip |
| 561 | Sync DR with Primary and trouble shooting | Some times there are issues with DR where we dont see MRP and RFS process running , Need to troubleshoot in the following order 1) Verfiy the mount po |
| 562 | Relink using adadmin/manually in EBS | Problem: Relink file mismatch References: Symptoms:Issue observed with MSC, MSO and MSR executables inbetween lib and bin Causes: version mismatch obs |
| 519 | Demantra clone document | Demantra clone document Demantra Cloning Procedure Reference: Tips on Cloning a Demantra Schema/Database (ex. refreshing the DEV environment with PRD  |
| 606 | Kill run away SQL in database | Title : Kill run away delete References : https://stackoverflow.com/questions/622289/how-to-check-oracle-database-for-long-running-queries Procedure o |
| 467 | Ryman clone procedure | DB Cloning Notes : -- Update 12/21/2018 : Added boomi user creation. For DB cloning the preferred method is rman duplicate , in case that controlfile  |
| 600 | Copy the GOLDB Workspace into a new workspace called EVOKE with everything in it | Title : Copy the GOLDB workspace into a new workspace called EVOKE with all the applications and data inside References : Export / Import for Laxmi--N |
| 574 | DEMANTRA 7.3.1 Bounce | 1. Connect to ASCP instance and choose option 3 [root@c1prdascp1 ~]# su - oraprod 1) APPS 2) RDBMS 3) DEMANTRA Please select environment: 3 STOP SERVI |
| 576 | Ryman : User Access Audit Report | Login to db node and execute script: [oraprd@rhdb1 ~]$ sqlplus / as sysdba @user_audit_report.sql Provide output generated. I.e : user_audit_report_20 |
| 569 | RYMAN ICX:Session Timeout | After Instance maintenance : Autoconfig , Bounce , patching . Check ICX Session timeout profile. Sometimes it value is reset back to 30. change it to  |
| 575 | Enabling License for Inventory Optimization in ASCP | License Inventory Optimization Module Navigation: System Administrator =>Oracle Applications Manager => License Manager => License Products => License |
| 577 | Change MariaDB temp location. | Changed temporary location of mariadb : Default temporary location is /tmp which could be filled very fast , so changed to another location with more  |
| 623 | How to grant Super privilege to Mysql user | Reference: https://stackoverflow.com/questions/44015692/access-denied-you-need-at-least-one-of-the-super-privileges-for-this-operat Find current user  |
| 578 | MariaDB/Mysql cold backup. | Cold Backup : Stop mysql service : [root@af281 ~]# systemctl stop mariadb [root@af281 ~]# ps -ef \| grep mariadb root 12705 10443 0 13:41 pts/1 00:00: |
| 570 | Changing EBS 12.2.X passwords | Changing EBS 12.2.X passwords 1) Change of sys and system passwords using SQLPLUS command Source the DB environment and connect to database as sysdba  |
| 579 | Nano Cost Manager Troubleshooting. | 1. Find Cost manager req id #: SQL> SELECT request_id RequestId, 2 request_date RequestDt, 3 phase_code Phase, 4 status_code Status FROM 5 fnd_concurr |
| 581 | WF MAILER and Planning Manager failed due to archivelog filled up | Problem : WF mailer and Planning manager failed References : www.dba-oracle.com/sf_ora_00257_archiver_error_connect_internal_only_until_freed.htm Symp |
| 573 | APPSCHECK Diagnostics script. | Provided apscheck as follow. 1. Download latest apscheck version from Oracle note Doc ID 246150.1 2. upload script to server. 3. execute apscheck conn |
| 631 | Steps to run SQL Tuning Task | Step # 01: ========== select sql_id from v$session where sid = :x Step # 02: ========== SET SERVEROUTPUT ON declare stmt_task VARCHAR2(40); begin stmt |
| 587 | RMAN duplicate with noopen option | run { allocate channel C1 device type disk; allocate auxiliary channel C2 device type disk; allocate auxiliary channel C3 device type disk; DUPLICATE  |
| 588 | 911: eca1db6 - imax -- imax_archive-eca1db6: Problem with Omni archive backup Alert fixing | 911: eca1db6 - imax -- imax_archive-eca1db6: Problem with Omni archive backup Alert fixing Please follow the below steps 1) Login in to eca1db6( 172.1 |
| 586 | Disable Windows local user password expiration | Title : Disable Windows user password expiration References : https://www.sevenforums.com/tutorials/73224-password-expiration-change-max-min-password- |
| 590 | Apex (AF3) stop/start procedure | Shutdown procedure: 1. Stop Tomcat : login to af3.appsforte.com as oraapex user : cd /u01/oraapex/tomcat/apache-tomcat-7.0.70/bin ./shutdown.sh 2. Sto |
| 591 | Add schema to workspace in APEX | Follow the steps in the google document. https://docs.google.com/document/d/1hd6udIkG-4CvyxbUpE98Giffq37iM9nbIXuvl92kRMw/edit |
| 585 | ICHOR clone | Check appsforte clone notes : https://docs.google.com/document/d/1-26WvvtiQeQqNLQltopIhM8mdq9Zmlc4nS1_f6k8j-c/edit Oracle E-Business 11i Refresh Steps |
| 592 | Unable to open Demantra 7.3 Worksheet | Problem : Unable to open Demantra 7 worksheet using URL : http://c1prdascp1.nanometrics.com:7010/demantra/portal/login.jsp References : Symptoms : Jav |
| 593 | Deployment of ssh keys for Bank of America (includes convert to hash host entries) | Below are the steps 1) download the ssh key files provided by source bank 2) copy it to server and unzip the file $ cd 66189 [eca1db11:itest /newitest |
| 594 | Discoverer prd file | Problem : Couldn't connect to Oracle Discoverer References : Symptoms : Couldn't connect to Oracle Discoverer Causes : dbc file was missing or incorre |
| 595 | Fwd: Workflow Notification Mailer is currently DOWN. Please contact Application Admin.Trying to brin | Problem :Workflow Mailer Notification DOWN References : Symptoms : Workflow Mailer Notification goes down in production from time to time without any  |
| 596 | To Unlock and reset INTERNAL ADMIN account in Apex 18.2 | Steps to unlock ADMIN account 1) source the database env file 2) connect to database as sysdba 3)Verify if the account is locked SQL> select USER_ID,  |
| 598 | Oracle database password file | Problem: Getting password error while logging in as SYSDBA@database. ORA-01017: invalid username/password; logon denied Solution: Create password file |
| 599 | Getting Error in Vertex Test http://c1tstebsapp1.nanometrics.com:8095/vertex-ui/vertextcc.jsp#dmData | Problem : Out of memory issue while accessing TEST Vertex References : Symptoms : Out of memory error messages in "/u01/VERTEX/tomcat/log/catalina.out |
| 645 | Create Guest Account in Office 365 for External domain users | Inviting organization side: Option 1: From O365 Admin Center 1. Log in as global administrator to Microsoft 365 admin center 2. Click Guest users menu |
| 601 | System down time on 5/25 @1am - 2am EST | Title : Shutdown / Startup web listener References : Procedure or Description : To Stop: /u01/app/oraweb/Middleware/Oracle_Home/user_projects/domains/ |
| 602 | Startup/shutdown EBS apps and db | Application : [apptest@c1tstebsapp1 scripts]$ adstpall.sh apps/apps [apptest@c1tstebsapp1 scripts]$ adstrtal.sh apps/apps Database : [oratest@c1tstebs |
| 608 | Monitor Backups and their progress | TO monitor RMAN backups , connect to SQLPLUS as sysdba and execute the below any of the queries Set lines 250 alter session set nls_date_format='DD-MO |
| 580 | Install PLPDF SDK | 1. Download PL/PDF SDK ( https://www.plpdf.com/downloads-v2.php ) and upload to Apex server. [oracle@af18 ~]$ pwd /home/oracle [oracle@af18 ~]$ ls -lt |
| 605 | WEBADI excel 2013 journal upload issue | WEBADI excel 2013 journal upload issue: when ever there is issue with journal upload first observe the BNE staging directory and see if we have enough |
| 532 | Copy from source Workspace/Applications to New Workspace/Applications -Apex | Step1 : Export the Applications and schema for the workspace to be copied DATA_PUMP_DIR = /u01/oraapex/admin/APEXPRD/dpdump/ SQL> select default_table |
| 607 | DR - archive log location full | Symptoms: 1. DR is out of sync. 2. archive log location on primary is full. Cause: There is a lot of activity on primary database, generating a lot of |
| 568 | Restore and refresh Brightfield database | Title : Refresh Brightfield DB References : Procedure or Description : 1. Shut down target database and clean up data files. 2. Clone database from so |
| 610 | Ichor - Unable to print | Problem : iChor - Unable to print References : Symptoms : Client is unable to print on the printer Causes : Printer is disabled. Solution : 1. Log on  |
| 557 | Error with opening log and out file for concurrent request | Below is the error and Solution :Copied tnsnames.ora file from c1stgebsapp1 to c1stgebsapp2 as below , [appstg@c1stgebsapp1 admin]$ scp tnsnames.ora c |
| 564 | Give RDP access to the windows machine - Remote Desktop | Title : Add Kyle to af194 RDP References : Procedure or Description : 1. Create Windows local user. 2. Allow remote access to new user. |
| 609 | RYMAN updated clone document --- NEW | Running Pre-Clone on the Source Apps Tier -- If required and if any patches that are applied only in source Running Pre-Clone on the Source DB Tier -- |
| 612 | Change long running jobs alert for ichor | Title : Customer requested to receive the alert only job is running References : Procedure or Description : Below steps were taken to make the request |
| 618 | Download language XLIFF files | From time to time, we get a request from Laxmi to download XLIFF translation files for her applications. **NOTE: we follow below steps from remote des |
| 613 | Brightfield - Create new SFTP folder | Notes: 1. Below example based on client user “clyde”. 2. Production SFTP user on af165. 3. SSH to server on port 222 since 22 is used by SFTP. Option  |
| 460 | Nanometrics : Vertex post-clone steps | Attached doc. To verify Vertex , we can use below command , ​ -From the server command prompt curl http://server:port/vertex-ws/adminservices/HealthCh |
| 614 | Clear lock or blocking session(s) | End-user complains about slow performance or may ask to kill a session directly on the database. Either way, please use the below procedure: 1. Log on |
| 481 | How to set up SSL on WebLogic | Set up SSL for WebLogic 1. First we need to create .pem file with the crt and intermediate files. Concat those like this [root@ah199 ~]# cat 2b699e9cb |
| 538 | Ryman Quarterly Audit scripts | Important -> select SYS_CONTEXT ('USERENV', 'LANG') from dual; ---> must return 'US' for EBS views to return data If above query doesn't return "US",  |
| 622 | New proxy setting uat_randstad_batch | Title : Add New Proxy for TDX References : Procedure or Description : 1. Log in to af165 2. Modify /etc/httpd/conf/httpd.conf ProxyPass /uat_randstad_ |
| 616 | Kill request in E-Business | Cancel request(s) from the front end. If you try to cancel any request from the front end, which has been running for a long time, it may/can error ou |
| 617 | Clear requests after bouncing FNDOPP | https://docs.google.com/document/d/1QjpdJVnDmUX0KJLwxCEmnyiQnIwPjvP6PnH-7HhQTSo/edit Clear up requests after FNDOPP problem We get below alert for FND |
| 555 | RYMAN Resolution on Excel4apps GL Wand | Resolution on glwand : After upgrade of GLWAND in R12.2 because of file version difference between patch file system and run file system we are facing |
| 544 | EBS Patching for 12.2.X | Reference : https://ora-data.blogspot.in/2016/12/step-by-step-to-apply-patches-in-oracle.html As we are knowing that applying patch in oracle EBS R12. |
| 560 | Schedule 360 (TSAD) demo refresh | [orarac@ah249 ~]$ sqlplus sys/S3cr3t123@tsad SQL*Plus: Release 12.1.0.2.0 Production on Sun Feb 4 20:16:55 2018 Copyright (c) 1982, 2014, Oracle. All  |
| 620 | E-Business Kill session using a specific form | 1. Set the environment accordingly. 2. Log onto the database using APPS credentials as below: sqlplus apps/ 3. Check the sessions using a specific for |
| 491 | Ryman More4Apps Installation | http://horizon.more4apps.com/doco/GeneralDocs/InstallationSetup/output/index.htm?sync&page=html/Steps-InstallationandConfiguration.html Products used  |
| 625 | Reset apex internal admin password | Method 1: Reset password via script apxchpwd.sql: [orarac@af356 apex]$ pwd /u01/app/apex/apex [orarac@af356 apex]$ sqlplus sys as sysdba SQL*Plus: Rel |
| 551 | Terminating ADOP activities in PROD. | Problem : Customer reported performance problems in the PROD Instance. References : Symptoms : General Slowness in GLWAND Causes : ADOP session runnin |
| 634 | Workflow Mailer Down (not running) alert | Problem: ======== Received alert for WF mailer being down on PRODEBS. Solution: ======== Log onto the below URL as sysadmin user: http://c1prdebsapp.n |
| 582 | Add New URL Monitoring to Monitis | Title : Add new monitis URL monitoring References : Procedure or Description : 1. Log in to Monitis.com. 2. Choose menu Monitors -> UptimeMonitors ->  |
| 589 | Enable ping on Windows server | References : https://kb.iu.edu/d/aopy Procedure or Description : Open Windows Firewall Click Advanced Settings on the left. From the left pane of the  |
| 619 | Cron jobs fail while reading data files. | Problem : Scheduled Oracle cron jobs failing to reading data file References : VDS 6.0.2 DSM Installation Guide Symptoms : Recovery Manager: Release 1 |
| 621 | How to Force Log Off Demantra User | Problem : User getting Already Logged in error when trying to log in References : How to Automatically Clear a Hunging Session ? (Doc ID 1994389.1) ht |
| 584 | Jobs to disable in Schedule 360 database after refreshing database to a new server | Need to connect as user tsa and run below ========= Dynamically disable all TSA jobs =========================================================​ DECLAR |
| 635 | Pending Concurrent Jobs count alert | Problem: ======== We receive the alert for number of pending concurrent jobs being HIGH. Symptoms: ========== There could be multiple reasons for a hi |
| 644 | JAR Resources In JNLP File Are Not Signed By Same Certificate | Reference: E-Business Suite R12.2 : Using Java Web Start Get Error 'JAR Resources In JNLP File Are Not Signed By Same Certificate' (Doc ID 2464716.1)  |
| 646 | Create Shared folder in Microsoft 365 and assign Members | Create new Shared folder: 1. Log in as global admin to SharePoint admin site. 2. Click Sites -> Active sites. 3. Click Create and select Team site, th |
| 647 | Generate Report of Active Users Sorted by Last Login Time | Title : Generate Active Users report sorted by login time References : Symptoms (if applicable) : Causes (if applicable) : Procedure or Solution Descr |
| 45 | New APEX account provisioning | Provision new APEX account Follow solution 26 to create new workspace and schema Follow soution 27 to provision CPANEL account and setup DNS Test new  |
| 648 | Creating CDN Server with NGINX | Eric's documentation of step-by-step information for the current standard CDN server setup. |
| 649 | VM very high load average with high IO, unresponsive, won't come back online | References : Symptoms (if applicable) : Causes (if applicable) : Procedure or Solution Description : 1. Force shutdown 2. Copy to another VM, fast clo |
| 650 | EBS Forms not opening for client | Title : CLIENT UNABLE TO OPEN JAR FORM References : Symptoms (if applicable) : Causes (if applicable) : Procedure or Solution Description : Inform cli |
| 656 | Changing Password for EBS level user using FNDCPASS | Title : How to change password for EBSapps user with FNDCPASS References : Symptoms (if applicable) : Client does not know or forgot password to login |
| 652 | Setting up Inventory Debugger for EBS | Title : Setup Inventory Debugger for EBS References : https://support.oracle.com/epmos/faces/DocumentDisplay?_afrLoop=280020470807269&parent=SrDetailT |
| 651 | Adding Analyzer Scripts as Concurrent Request to EBS | Title : Adding Analyzer as Concurrent Request to EBS References : Symptoms (if applicable) : Causes (if applicable) : Procedure or Solution Descriptio |
| 653 | Create Synonym in Oracle Database | Title : Create Synonym in Oracle Database References : Symptoms (if applicable) : Causes (if applicable) : Procedure or Solution Description : You mus |
| 654 | Get Package Files from Database | Title : Get Package files from EBS References : Symptoms (if applicable) : Causes (if applicable) : Procedure or Solution Description : Connect to the |
| 655 | Stop log files from generating for external tables, (disable stats monitoring) | Title : Stop log files from generating for external tables, (disable stats monitoring) References : https://forums.oracle.com/ords/apexds/post/externa |
| 657 | Adding a Program or Script as concurrent program in EBS | To figure out which request groups have access to the script/application, submit a new request with the name of "Report Group Responsibilities". For t |
| 658 | Run or execute given script on EBS instance | Title : Run or execute given script on EBS instance References : Symptoms (if applicable) : Causes (if applicable) : Procedure or Solution Description |
| 139 | Create a CSR for Certificate with openssl | Create a CSR for Certificate 1. Log on to the server and execute "openssl genrsa" command followed by below options: [root@haproxy1 2024]# openssl gen |
| 659 | Migrating packages onto production - Backing up before executing migration script | Title : Migration packages for production - Executing scripts to update data in EBS References : Symptoms (if applicable) : Causes (if applicable) : P |
| 660 | Get or find OPP logs for failed concurrent request. FNDOPP | Title : Get or find OPP logs for failed concurrent request. FNDOPP References : Symptoms (if applicable) : Causes (if applicable) : Procedure or Solut |
| 661 | Finding and grabbing .rdf reports for XXONTO | Title : Finding and grabbing .rdf reports for XXONTO References : Symptoms (if applicable) : Causes (if applicable) : Procedure or Solution Descriptio |
| 662 | Granting permissions or privileges to schema/user | Title : Granting permissions or privileges to schema/user References : Symptoms (if applicable) : Causes (if applicable) : Procedure or Solution Descr |
| 663 | Cannot edit trusted list / Cannot open forms EBS apps | Title : OntoInnovation Computers unable to open EBS application forms References : Symptoms (if applicable) : Cannot add the url/host to Java security |
| 664 | Running Analyzer script on EBS | Title : Running Analyzer script on EBS References : Symptoms (if applicable) : Causes (if applicable) : Procedure or Solution Description : Download t |
| 665 | Create Missing Sequence Synonyms for APPSREAD user | Title : Create Missing Sequence Synonyms for APPSREAD user References : see sequence synonym creation on this document: https://docs.google.com/docume |
