#! /usr/bin/env python
# all constants containing a value "set to ..." are to be customized
#
import smtplib, sys, datetime, time
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText

import datetime, glob, os

EMAIL_EDITOR_FROM = "dft-no-reply@evidenceproject.eu"
EMAIL_EDITOR_FROM_NAME = "EVIDENCE Project: Forensics Tools Catalogue - Editor"
EMAIL_APPROVAL_FROM_NAME = "EVIDENCE Project: Forensics Tools Catalogue - Editor Refusal"
EMAIL_EDITOR_ADMIN = "...emailUserAdmin"
EMAIL_EDITOR_ADMIN_PWD = "...emailUserAdminPassword"
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

if len(sys.argv) < 5:
    fLog.write(sOggi + ' ' + sOra + ' - Usage: ' + sys.argv[0] + ' toolName userTo userToEmail motivation' + "\n")
    sys.exit(100);

toolName 	= sys.argv[1]
userName 	= sys.argv[2]
userEmail	= sys.argv[3]
motivation	= sys.argv[4]

emailMsg = MIMEMultipart('alternative')

emailMsg['Subject'] = EMAIL_APPROVAL_FROM_NAME
emailMsg['From'] = EMAIL_EDITOR_FROM	
emailMsg['To'] = userEmail

#emailMsg['Cc'] = timeUser
#emailMsg['Bcc'] = "fabrizio.turchi@gmail.com"

emailBody  = "<p>Dear " + userName + "<br/><br/>we are sorry to inform you tha the following tool </p>"
emailBody += "<strong>" + toolName + "</strong><br/><br/> has just been refuse! <br/><br/>"
emailBody += "The motivation is the following <br/><br/> ";
emailBody += "<blockquote>" + motivation + "</blockquote><br/><br/>Best Regards";

part = MIMEText(emailBody, 'html')
emailMsg.attach(part)
		
try:
#--- server = smtplib.SMTP("smtp.gmail.com", 587)
	server = smtplib.SMTP_SSL(EMAIL_SMTP_HOST, EMAIL_SMTP_PORT) 
	server.login(EMAIL_EDITOR_ADMIN, EMAIL_EDITOR_ADMIN_PWD) 
	server.sendmail(emailMsg['From'], [emailMsg['To'], emailMsg['Cc'], emailMsg['Bcc']], emailMsg.as_string())
	server.quit()
	fLog.write(sOggi + ' ' + sOra + ' - successfully sent the refusal mail: ' + sys.argv[1] + ' ' + sys.argv[2] + '\n')
except:
	fLog.write(sOggi + ' ' + sOra + ' - failed to send the refusal mail: ' + sys.argv[1] + ' ' + sys.argv[2] + '\n')


fLog.close()
        