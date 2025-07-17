# Email Settings Quick Reference

## Gmail
```
IMAP Server: imap.gmail.com
IMAP Port: 993
IMAP Encryption: SSL
SMTP Server: smtp.gmail.com
SMTP Port: 587
SMTP Encryption: TLS
Username: your-email@gmail.com
Password: [App Password - 16 characters]
```

**Prerequisites:**
- Enable 2-Step Verification
- Generate App Password
- Enable IMAP in Gmail settings

## Microsoft Outlook / Office 365
```
IMAP Server: outlook.office365.com
IMAP Port: 993
IMAP Encryption: SSL
SMTP Server: smtp-mail.outlook.com
SMTP Port: 587
SMTP Encryption: TLS
Username: your-email@outlook.com
Password: [App Password]
```

**Prerequisites:**
- Enable Two-Factor Authentication
- Create App Password

## Yahoo Mail
```
IMAP Server: imap.mail.yahoo.com
IMAP Port: 993
IMAP Encryption: SSL
SMTP Server: smtp.mail.yahoo.com
SMTP Port: 587 or 465
SMTP Encryption: TLS (587) or SSL (465)
Username: your-email@yahoo.com
Password: [App Password]
```

## iCloud Mail
```
IMAP Server: imap.mail.me.com
IMAP Port: 993
IMAP Encryption: SSL
SMTP Server: smtp.mail.me.com
SMTP Port: 587
SMTP Encryption: TLS
Username: your-email@icloud.com
Password: [App-specific password]
```

## AOL Mail
```
IMAP Server: imap.aol.com
IMAP Port: 993
IMAP Encryption: SSL
SMTP Server: smtp.aol.com
SMTP Port: 587
SMTP Encryption: TLS
Username: your-email@aol.com
Password: [App Password]
```

## Zoho Mail
```
IMAP Server: imap.zoho.com
IMAP Port: 993
IMAP Encryption: SSL
SMTP Server: smtp.zoho.com
SMTP Port: 465
SMTP Encryption: SSL
Username: your-email@zoho.com
Password: [Account Password or App Password]
```

## ProtonMail (Bridge Required)
```
IMAP Server: 127.0.0.1
IMAP Port: 1143
IMAP Encryption: None (Bridge handles encryption)
SMTP Server: 127.0.0.1
SMTP Port: 1025
SMTP Encryption: None (Bridge handles encryption)
Username: your-email@protonmail.com
Password: [Bridge Password]
```

**Note:** ProtonMail requires ProtonMail Bridge application

## Custom Domain (cPanel/Plesk)
```
IMAP Server: mail.yourdomain.com
IMAP Port: 993 (SSL) or 143 (non-SSL)
IMAP Encryption: SSL or None
SMTP Server: mail.yourdomain.com
SMTP Port: 465 (SSL) or 587 (TLS) or 25 (non-SSL)
SMTP Encryption: SSL/TLS or None
Username: your-email@yourdomain.com
Password: [Email Password]
```

## Quick Troubleshooting

### Gmail Issues
- **Less secure apps**: May need to enable temporarily
- **CAPTCHA**: Visit https://accounts.google.com/DisplayUnlockCaptcha
- **IMAP not enabled**: Check Gmail settings → Forwarding and POP/IMAP

### Outlook Issues
- **Modern Auth**: Use app password, not regular password
- **Aliases**: Use primary email address as username

### General Issues
- **Firewall**: Ensure ports are not blocked
- **Antivirus**: May interfere with email connections
- **ISP Blocking**: Some ISPs block port 25, use 587 or 465

## Port Reference

### IMAP Ports
- **143**: IMAP (no encryption)
- **993**: IMAP over SSL/TLS

### SMTP Ports
- **25**: SMTP (no encryption) - Often blocked by ISPs
- **465**: SMTP over SSL
- **587**: SMTP over TLS (Recommended)
- **2525**: Alternative SMTP (some providers)

## Encryption Types
- **SSL**: Secure Sockets Layer (older, still widely used)
- **TLS**: Transport Layer Security (newer, recommended)
- **STARTTLS**: Upgrades plain connection to encrypted
- **None**: No encryption (not recommended)
