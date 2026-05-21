import Location from "./Location.js";

class Runway extends Location {
  constructor(locationId, isAvailable, runwayId) {
    super(locationId, isAvailable);
    this.runwayId = runwayId;
  }
}

export default Runway;
