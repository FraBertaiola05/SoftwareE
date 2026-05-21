import Location from "./Location.js";
import Flight from "./Flight.js";

class Airport {
  constructor(name, locations, landingFlights, departingFlights) {
    this.name = name;
    this.locations = locations;
    this.landingFlights = landingFlights;
    this.departingFlights = departingFlights;
  }

  updateScreens() {}
}

export default Airport;
