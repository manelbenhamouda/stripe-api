import { useParams } from "react-router-dom";
import { useEffect, useState } from "react";
import axios from "axios";

export default function Success() {
  const { orderId } = useParams();
  const [order, setOrder] = useState(null);

  useEffect(() => {
    axios.get(`http://localhost:8000/api/payment/success/${orderId}`)
      .then(res => setOrder(res.data.order))
      .catch(err => console.error("Erreur récupération commande :", err));
  }, [orderId]);

  return (
    <div>
      <h1>Paiement réussi </h1>
      {order ? (
        <div>
          <p>Commande #{order.id} confirmée</p>
          <p>Total : {(order.total / 100).toFixed(2)} €</p>
          <p>Statut : {order.status}</p>
        </div>
      ) : (
        <p>Chargement en cours...</p>
      )}
    </div>
  );
}