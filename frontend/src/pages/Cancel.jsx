import { useParams } from "react-router-dom";
import { useEffect } from "react";
import axios from "axios";

export default function Cancel() {
  const { orderId } = useParams();

  useEffect(() => {
    axios.get(`http://localhost:8000/api/payment/cancel/${orderId}`)
      .then(() => console.log("Commande annulée."))
      .catch(err => console.error("Erreur annulation :", err));
  }, [orderId]);

  return (
    <div>
      <h1>Paiement annulé </h1>
      <p>Votre commande a été annulée.</p>
    </div>
  );
}