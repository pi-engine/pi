
Pi Engine User Authentication

Service:
- auth

Adapters:
- system
- local
- ssp (simplesamlphp)

URIs: Per adapter
- login
- logout
- register

APIs: Per adapter
- bind: load current user session
- hasIdentity: check if current user authenticated
- getIdentity: get current user identity
- clearIdentity: clear current user session
- authenticate: process authentication
- getData: get embedded user profile data
- requireAuthentication: check against identity and redirect to login if not