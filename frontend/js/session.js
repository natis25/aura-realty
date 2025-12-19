// session.js - unificado y con logs para debug
(function () {
  function parseUser() {
    try {
      const raw = localStorage.getItem("user");
      if (!raw) return null;
      return JSON.parse(raw);
    } catch (e) {
      console.error("session.js: error parseando user en localStorage", e);
      return null;
    }
  }

  // Devuelve usuario o null
  window.getUser = function () {
    return parseUser();
  };

  // Borra sesión y redirige al login
  window.logout = function () {
    console.log("session.js: logout() ejecutado - limpiando localStorage");
    localStorage.removeItem("user");
    // forzar recarga desde la ruta absoluta
    window.location.href = "/TALLER/aura-realty/frontend/login.html";
  };

  // Valida sesión y rol (si requiredRole se proporciona)
  window.checkAuth = function (requiredRole = null) {
    const user = parseUser();
    console.log("session.js: checkAuth() -> user:", user, "requiredRole:", requiredRole);

    if (!user) {
      console.warn("session.js: usuario no encontrado en localStorage -> redirigiendo a login");
      window.location.href = "/aura-realty/aura-realty/frontend/login.html";
      return false;
    }

    if (requiredRole && user.rol !== requiredRole) {
      console.warn(`session.js: rol incorrecto (esperado=${requiredRole}, real=${user.rol}) -> redirigiendo a login`);
      window.location.href = "/aura-realty/aura-realty/frontend/login.html";
      return false;
    }

    // si llegó aquí, la sesión está OK
    console.log("session.js: sesión válida para", user.nombre, user.rol);
    return true;
  };

  // pequeño helper para debug desde consola
  window._debugSession = function () {
    console.log(">>> localStorage.user:", localStorage.getItem("user"));
    console.log(">>> parsed:", parseUser());
  };
})();
