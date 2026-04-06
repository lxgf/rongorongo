vcl 4.1;

import std;

backend default {
    .host = "openresty";
    .port = "80";
    .connect_timeout = 5s;
    .first_byte_timeout = 60s;
    .between_bytes_timeout = 10s;
}

# Called at the start of a request, after it has been fully received
sub vcl_recv {
    # Pass admin panel — never cache
    if (req.url ~ "^/admin") {
        return (pass);
    }

    # Pass authenticated Filament sessions
    if (req.http.Cookie ~ "filament_") {
        return (pass);
    }

    # Forward original client info to backend
    if (req.http.Host ~ ":(\d+)$") {
        set req.http.X-Forwarded-Port = regsub(req.http.Host, "^.*:(\d+)$", "\1");
    } else {
        set req.http.X-Forwarded-Port = "80";
    }
    if (!req.http.X-Forwarded-Proto) {
        set req.http.X-Forwarded-Proto = "http";
    }

    # Pass non-GET/HEAD requests
    if (req.method != "GET" && req.method != "HEAD") {
        return (pass);
    }

    # Pass CSRF / XSRF requests
    if (req.http.X-CSRF-Token || req.http.X-XSRF-TOKEN) {
        return (pass);
    }

    # Strip cookies for static assets — allow caching
    if (req.url ~ "\.(gif|GIF|png|jpg|jpeg|webp|svg|ico|css|js|woff|woff2|ttf|eot)(\?.*)?$") {
        unset req.http.Cookie;
        return (hash);
    }

    # For everything else with cookies — pass through
    if (req.http.Cookie) {
        return (pass);
    }

    return (hash);
}

# Called after the response headers have been received from the backend
sub vcl_backend_response {
    # Cache static assets for 1 year
    if (bereq.url ~ "\.(gif|GIF|png|jpg|jpeg|webp|svg|ico|css|js|woff|woff2|ttf|eot)(\?.*)?$") {
        set beresp.ttl = 365d;
        set beresp.grace = 1d;
        unset beresp.http.Set-Cookie;
        return (deliver);
    }

    # Don't cache admin responses
    if (bereq.url ~ "^/admin") {
        set beresp.uncacheable = true;
        set beresp.ttl = 0s;
        return (deliver);
    }

    # Don't cache responses that set cookies (login pages etc.)
    if (beresp.http.Set-Cookie) {
        set beresp.uncacheable = true;
        set beresp.ttl = 0s;
        return (deliver);
    }

    # Cache public pages for 60s
    if (beresp.status == 200) {
        set beresp.ttl = 60s;
        set beresp.grace = 30s;
    }

    return (deliver);
}

# Called before the response is delivered to the client
sub vcl_deliver {
    # Add cache status header for debugging
    if (obj.hits > 0) {
        set resp.http.X-Cache = "HIT";
        set resp.http.X-Cache-Hits = obj.hits;
    } else {
        set resp.http.X-Cache = "MISS";
    }

    # Remove internal headers
    unset resp.http.X-Powered-By;
    unset resp.http.Server;
}

# Called when a cached object is selected for a request
sub vcl_hit {
    if (obj.ttl >= 0s) {
        return (deliver);
    }
    # Stale content — deliver while refreshing in background
    if (obj.ttl + obj.grace > 0s) {
        return (deliver);
    }
    return (restart);
}
