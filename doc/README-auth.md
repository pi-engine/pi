
Pi Engine User Authentication

Service:
- authentication

Strategy:
- Local
- Saml (simplesamlphp)

URIs: Per adapter
- login
- logout

APIs: Per adapter
- getUrl
- bind: load current user session
- hasIdentity: check if current user authenticated
- getIdentity: get current user identity
- clearIdentity: clear current user session
- authenticate: process authentication
- getData: get embedded user profile data
- requireLogin: check against identity and redirect to login if not
- login
- logout