import PlaneStatus from "./PlaneStatus.js";

class Plane {
  constructor(planeId, status) {
    this.planeId = planeId;
    this.status = status;
  }

  updatePosition(newLocation) {}
}

export default Plane;
