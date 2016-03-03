#! /usr/bin/env python
import smtplib, sys, datetime, time
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText

import datetime, glob, os

EMAIL_EDITOR_FROM = "dft-no-reply@evidenceproject.eu"
EMAIL_EDITOR_FROM_NAME = "EVIDENCE Project: Forensics Tools Catalogue - Editor"
EMAIL_EDITOR_UPDATE_SUBJECT = "EVIDENCE Project: Forensics Tools Catalogue, updating tool"
EMAIL_EDITOR_INSERT_SUBJECT = "EVIDENCE Project: Forensics Tools Catalogue, insert new tool"
EMAIL_EDITOR_ADMIN = "...emailUserAdmin"
EMAIL_EDITOR_ADMIN_PWD = "...emailUserAdminPassword"
#EMAIL_SMTP_HOST = "ssl://mail.ittig.cnr.it"
EMAIL_SMTP_HOST = "...smtpHost"
EMAIL_SMTP_PORT = 465


oggi = datetime.datetime.today()

sDay = str(oggi.day)
sMonth = str(oggi.month)
sYear = str(oggi.year)

if len(sDay) < 2:
	sDay = '0' + sDay 	

if len(sMonth) < 2:
	sMonth = '0' + sMonth

sOggi = sYear + sMonth + sDay
sOra  = time.strftime("%H:%M:%S")



fLog = open(os.getcwd() + "/debug/dfte.send.mail.log", "a")

if len(sys.argv) < 6:
    fLog.write(sOggi + ' ' + sOra + ' - Usage: ' + sys.argv[0] + ' toolName user userEmail operation ltsEmailAdmins' + "\n")
    sys.exit(100);

toolName 	= sys.argv[1]
userName 	= sys.argv[2]
userEmail	= sys.argv[3]
operation	= sys.argv[4]
emailAdmin 	= sys.argv[5]

emailMsg = MIMEMultipart('alternative')

if operation == "U":
	emailMsg['Subject'] = EMAIL_EDITOR_UPDATE_SUBJECT
	emailOperation = " updated by <br/>"
else: 
	if operation == "I":
		emailMsg['Subject'] = EMAIL_EDITOR_INSERT_SUBJECT
		emailOperation = " inserted by <br/>"
	else:
		fLog.write(sOggi + ' ' + sOra + ' - Operation parameter is wrong, values admissable are U or I, value=' + operation + "\n")
		sys.exit(100)
	


emailMsg['From'] = EMAIL_EDITOR_FROM	
#emailMsg['To'] = EMAIL_EDITOR_ADMIN
#emailMsg['To'] = ", ".join(emailAdmins)
emailMsg['To'] = emailAdmin
#emailMsg['Cc'] = timeUser
#emailMsg['Bcc'] = "fabrizio.turchi@gmail.com"

emailBody  = "<p>The following tool </p>"
emailBody += "<strong>" + toolName + "</strong><br/><br/> has just been " + emailOperation + "<br/><br/>"
emailBody += "<strong>" + userName + "</strong> (" + userEmail + ") <br/><br/>Best Regards";

part = MIMEText(emailBody, 'html')
emailMsg.attach(part)
		
try:
#--- server = smtplib.SMTP("smtp.gmail.com", 587)
	server = smtplib.SMTP_SSL(EMAIL_SMTP_HOST, EMAIL_SMTP_PORT) 
	server.login(EMAIL_EDITOR_ADMIN, EMAIL_EDITOR_ADMIN_PWD) 
	#server.sendmail(emailMsg['From'], [emailMsg['To'], emailMsg['Cc'], emailMsg['Bcc']], emailMsg.as_string())
	server.sendmail(emailMsg['From'], [emailMsg['To'], emailMsg['Cc'], emailMsg['Bcc']], emailMsg.as_string())
	server.quit()
	sLine  = sOggi + ' ' + sOra + ' - successfully sent the mail: ' + sys.argv[1] + ' ' + sys.argv[2] + ' '
	sLine += sys.argv[3] + ' ' + sys.argv[4] + ' ' + sys.argv[5]
	fLog.write(sLine + '\n')
except:
	sLine  = sOggi + ' ' + sOra + ' - failed to send the mail: ' + sys.argv[1] + ' ' + sys.argv[2] + ' '
	sLine += sys.argv[3] + ' ' + sys.argv[4] + ' ' + sys.argv[5]
	fLog.write(sLine + '\n')


fLog.close()
        