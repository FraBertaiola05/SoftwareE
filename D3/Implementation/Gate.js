import Location from "./Location.js";

class Gate extends Location {
  constructor(locationId, isAvailable, gateNumber) {
    super(locationId, isAvailable);
    this.gateNumber = gateNumber;
  }

  assignFlight(flight) {}
}

export default Gate;
