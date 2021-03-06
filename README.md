<!-- GETTING STARTED -->
## Smart Driver Capacity Management
Smart driver capacity management to assign meal orders to delivery drivers, assigning more than one order to the same driver at the same time.


![Untitled drawing (1)](https://user-images.githubusercontent.com/4533327/122641653-5ffc0a80-d10f-11eb-9411-7f55ad2a4872.png)

Let's agree on glossaries below:
1. Trip : is a journey that Driver makes to a particular place ( Customer's location ).
2. Collection : is the driver basket that inlcudes set of orders. 

There are main 3 entities in the algorithm
1. Driver 
3. Restaurant 
2. Order and Order Item

When it comes to food, temperature is just as important as taste. No matter how delicious your juicy burger is, no one will take a bite if it’s as cold as ice. Transporting hot and cold foods together, in the same container, can compromise the safety and integrity of your meals. Train delivery staff to use insulated hot and cold bags to keep food at the appropriate temperature.

Database Structure:

<img width="971" alt="Screen Shot 2021-06-29 at 11 09 46 PM" src="https://user-images.githubusercontent.com/4533327/123861006-50f73280-d92f-11eb-9b37-1c5f6928fb2d.png">

Where 
1. prepare_tolerence_minutes = how many minutes you are allowed to wait between orders
2. collection_tolerence_minutes = how many minutes you are allowed to wait the whole collection before you leave the rest
3. max_agg_orders = Max orders per collection to get from rest before you leave. 
4. max_agg_order_items = Max order items per collection to get from rest before you leave. 
- here you take the minmum between max_agg_orders and max_agg_order_items. 
5. item_handling_seconds and order_handling_seconds = handling time per order and item order


### Installation
<img src="https://user-images.githubusercontent.com/4533327/122641086-ae0f0f00-d10b-11eb-856c-94ef6ba983da.png"/>
<img src="https://user-images.githubusercontent.com/4533327/122641090-b36c5980-d10b-11eb-8a9f-32b7c3b3b0d4.png"/>
<img width="1193" alt="Screen Shot 2021-06-19 at 2 42 59 PM" src="https://user-images.githubusercontent.com/4533327/122641251-c3d10400-d10c-11eb-9c4b-afffff2a9f9a.png">
<img width="1190" alt="Screen Shot 2021-06-19 at 2 43 14 PM" src="https://user-images.githubusercontent.com/4533327/122641253-c7fd2180-d10c-11eb-9cd1-f8042aaaecf4.png">
<img src="https://user-images.githubusercontent.com/4533327/122641091-b5361d00-d10b-11eb-80e7-9d129778117c.png"/>



<!-- LICENSE -->
## License

Distributed under the MIT License. See `LICENSE` for more information.



<!-- CONTACT -->
## Contact

Ramzi Alqrainy - [@RamziAlqrainy](https://twitter.com/RamziAlqrainy) - ramzi.alqrainy@gmail.com




