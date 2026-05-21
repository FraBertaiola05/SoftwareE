import User from "./User.js";
import Plane from "./Plane.js";
import Gate from "./Gate.js";
import Location from "./Location.js";
import ParkingSpot from "./ParkingSpot.js";

class GroundManagementSystem {
  getAvailableGates() {}

  linkPlaneToGate(user, plane, gate) {}

  updatePlanePosition(user, plane, location) {}

  markPlaneAsParked(user, plane, parkingSpot) {}
}

export default GroundManagementSystem;
