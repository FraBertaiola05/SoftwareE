import TransactionType from "./TransactionType.js";

class FinanceSystem {
  constructor(costOfLanding, parkingCost, priceToScheduleFlight) {
    this.costOfLanding = costOfLanding;
    this.parkingCost = parkingCost;
    this.priceToScheduleFlight = priceToScheduleFlight;
  }

  calculateAirportCosts(t1, t2) {}

  calculateAirportRevenue(t1, t2) {}

  getFinancialOverview(user, t1, t2) {}
}

export default FinanceSystem;
