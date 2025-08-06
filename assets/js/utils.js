function formatDateTime(datetimeStr) {
  const date = new Date(datetimeStr);

  // Ajusta para UTC-3 (Brasília)
  date.setHours(date.getHours() - 3);

  return new Intl.DateTimeFormat("pt-BR", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
    hour12: false,
  }).format(date);
}

function formatDateToDDMMYYYY(dateString) {
  // Split the input string into year, month, and day
  const [year, month, day] = dateString.split("-");

  // Create a Date object in UTC to avoid timezone issues
  const date = new Date(Date.UTC(year, month - 1, day));

  // Check if the date is valid
  if (isNaN(date.getTime())) {
    throw new Error("Invalid date string");
  }

  // Extract day, month, and year in UTC
  const utcDay = String(date.getUTCDate()).padStart(2, "0");
  const utcMonth = String(date.getUTCMonth() + 1).padStart(2, "0"); // Months are 0-based
  const utcYear = date.getUTCFullYear();

  // Return the date in DD/MM/YYYY format
  return `${utcDay}/${utcMonth}/${utcYear}`;
}

function removeAccents(str) {
  return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
}

function getDateAsHowLongFormatted(d) {
  const date = new Date(d);
  date.setHours(date.getHours() - 3);

  const now = new Date();

  const diff_seconds = (now.getTime() - date.getTime()) / 1000;

  if (diff_seconds < 60) {
    return "Agora";
  } else if (diff_seconds < 60 * 60) {
    return (diff_seconds / 60).toFixed(0) + "min";
  } else if (diff_seconds < 60 * 60 * 24) {
    return (diff_seconds / (60 * 60)).toFixed(0) + "h";
  } else if (diff_seconds < 60 * 60 * 24 * 30) {
    return (diff_seconds / (60 * 60 * 24)).toFixed(0) + "d";
  } else {
    return date.toLocaleDateString();
  }
}

function getSafeValue(fn, defaultValue) {
  try {
    return fn();
  } catch (e) {
    return defaultValue;
  }
}
