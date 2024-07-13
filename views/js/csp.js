document.addEventListener("DOMContentLoaded", function() {
    var meta = document.createElement('meta');
    meta.httpEquiv = "Content-Security-Policy";
    meta.content = "default-src 'self'; script-src 'self'";
    document.getElementsByTagName('head')[0].appendChild(meta);
});
