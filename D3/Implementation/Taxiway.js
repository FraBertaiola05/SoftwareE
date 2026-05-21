import Location from "./Location.js";

class Taxiway extends Location {
  constructor(locationId, isAvailable, code) {
    super(locationId, isAvailable);
    this.code = code;
  }
}

export default Taxiway;
