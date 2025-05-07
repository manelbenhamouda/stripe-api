import { useEffect, useState } from "react";
import axios from "axios";

export default function ProductList() {
  const [products, setProducts] = useState([]);

  useEffect(() => {
    axios.get("http://localhost:8000/api/products")
    .then(res => {
      console.log("Réponse Axios :", res);
      console.log("Produits reçus :", res.data.products);
      setProducts(res.data.products);
    })
    .catch(err => {
      console.error("Erreur Axios :", err);
    });
  }, []);

  const handleBuy = async (productId) => {
    try {
      const res = await axios.post("http://localhost:8000/api/checkout", {
        product_ids: [productId]
      });

      if (res.data.url) {
        window.location.href = res.data.url;
      }
    } catch (err) {
      console.error("Erreur lors de la création de la session Stripe :", err);
    }
  };

  return (
    <div>
      <h2>Produits</h2>
      <ul>
        {products.map(prod => (
          <li key={prod.id}>
            <strong>{prod.name}</strong> — {(prod.price / 100).toFixed(2)} €
            <button onClick={() => handleBuy(prod.id)} style={{ marginLeft: "10px" }}>
              Acheter
            </button>
          </li>
        ))}
      </ul>
    </div>
  );
}