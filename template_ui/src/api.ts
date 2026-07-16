const getApiBase = () => {
  let pathname = window.location.pathname;

  // Remove "index.html" from the end if it exists
  if (pathname.endsWith('index.html')) {
    pathname = pathname.slice(0, -10);
  }

  // Ensure pathname ends with a slash
  if (!pathname.endsWith('/')) {
    pathname += '/';
  }

  return window.location.origin + pathname + 'index.php/api';
};

export const API_BASE = getApiBase();
