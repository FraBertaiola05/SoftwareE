import TransactionType from "./TransactionType.js";

class FinancialLog {
  constructor(logId, amount, timestamp, type, description) {
    this.logId = logId;
    this.amount = amount;
    this.timestamp = timestamp;
    this.type = type;
    this.description = description;
  }
}

export default FinancialLog;
