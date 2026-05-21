import FlightStatus from "./FlightStatus.js";

class Flight {
  constructor(flightId, priority, scheduledTime, status) {
    this.flightId = flightId;
    this.priority = priority;
    this.scheduledTime = scheduledTime;
    this.status = status;
  }
}

export default Flight;
