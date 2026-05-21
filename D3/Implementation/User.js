import RoleEnum from "./RoleEnum.js";

class User {
  constructor(id, name, email, password, role) {
    this.id = id;
    this.name = name;
    this.email = email;
    this.password = password;
    this.role = role;
  }

  login() {}

  hasPermission(action) {}
}

export default User;
