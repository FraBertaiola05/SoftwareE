import Location from "./Location.js";

class ParkingSpot extends Location {
  constructor(locationId, isAvailable, parkingSpotId) {
    super(locationId, isAvailable);
    this.parkingSpotId = parkingSpotId;
  }
}

export default ParkingSpot;
