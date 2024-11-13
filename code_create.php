// user

http post localhost:3000/users firstname="gaspard" lastname="kirira" username="adastra" email="gaspard@gmail.com" password=12345678

// service

cuhttp post localhost:3000/services title="service 1" description="contenu" category="cat" image="1.png"

//sub services

curl -X POST localhost:3000/sub_services -H "Content-Type: application/json" -d '{"title": "Sous-service 1", "description": "Descriptio
odu sous-service", "price": "100", "image": "image.jpg"}'
{"message":"SubService created","id":"1"}

// order

curl -X POST localhost:3000/orders -H "Content-Type: application/json" -d '{
"user_id": 1,
"order_items": [
{"service_id": 1, "sub_service_id": null, "quantity": 2, "price": 100},
{"service_id": null, "sub_service_id": 1, "quantity": 1, "price": 50}
]
}'