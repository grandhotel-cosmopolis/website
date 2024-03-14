type Stage = "local" | "dev" | "prod";

export const getCurrentStage = (): Stage => {
  const currentLocation = window.location.href;
  if (currentLocation.includes("dev.grandhotel-cosmopolis.org")) {
    return "dev";
  }
  if (currentLocation.includes("grandhotel-cosmopolis.org")) {
    return "prod";
  }
  return "local";
};

export const getApiBaseAddress = (): string => {
  switch (getCurrentStage()) {
    case "local":
      return "http://127.0.0.1:8000";
    case "dev":
      return "https://dev.grandhotel-cosmopolis.org";
    case "prod":
      return "https://grandhotel-cosmopolis.org";
  }
};
