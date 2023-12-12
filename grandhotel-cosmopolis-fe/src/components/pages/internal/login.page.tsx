import { useState } from "react";
import { loginClient, retrieveCsrfToken } from "../../../infrastructure/api";
import { Button, Card, Input } from "@mui/material";

export const Login = () => {
  const [mail, setMail] = useState("");
  const [password, setPassword] = useState("");

  const retrieveCsrf = () => {
    retrieveCsrfToken();
  };

  const handleLogin = () => {
    loginClient.login(mail, password);
  };

  return (
    <Card>
      <Input placeholder="email" onChange={(e) => setMail(e.target.value)} />
      <Input type="password" onChange={(e) => setPassword(e.target.value)} />
      <Button onClick={retrieveCsrf}>Retrieve csrf</Button>
      <Button onClick={handleLogin}>Login</Button>
    </Card>
  );
};
