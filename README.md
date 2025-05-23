# otpaccessctl


## Sign-up (Account Issuance) Flow

### Authentication method: MailDomain

 signup.php -> signup_auth.php -> signup_verify.php
                   |
                   +-> mail -> signup_setcookie.php
                                     |
                                     +-> signup_confirm.php -> signup_auth2nd.php

### Authentication method: LDAP 

 signup.php -> signup_auth.php -> signup_verify.php
                   |
                   +-> mail -> signup_setcookie.php
                                     |
                                     +-> signup_confirm.php -> signup_auth2nd.php

## Sign-in (Pass Issuance) Flow

### Authentication method: MailDomain

 signin.php -> signin_auth.php -> signin_verify.php
                   |
                   +-> mail -> signin_setcookie.php
                                     |
                                     +-> signin_confirm.php -> signin_auth2nd.php -> signin_complete.php
                                                                     |
                                                                     +-> mail (if signout is enabled)

### Authentication method: LDAP 

 signin.php -> signin_auth.php -> signin_complete.php

## Sign Out Flow

 signout.php -> signout_auth.php -> signout_complete.php

 mail -> signout_setocookie.php
                |
                +-> signout.php -> signout_auth.php -> signout_complete.php

## Flow of Account

 --[authentication]--> account unauthenticated --[authentication 2nd]--> account valid

## Flow of Pass

 --[OTP authentication]--> pass unauthenticated --[authentication 2nd]--> pass inactive --[activation]--> pass active

 --[MFA authentication]-------------------------------------------------> pass inactive --[activation]--> pass active



