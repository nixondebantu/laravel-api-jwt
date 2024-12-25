### **API Overview**

This API facilitates auction management, user authentication, team creation, and player bidding. Below are the categorized functionalities:

---

### **Authentication**

#### **Register**

-   **Endpoint**: `POST /api/register`
-   **Body**:
    ```json
    {
        "fullname": "Your Name",
        "registration_number": "Unique Reg No.",
        "email": "your_email@example.com",
        "contact": "Your Contact Number",
        "password": "your_password"
    }
    ```
-   **Response**: Returns user details and an access token.

#### **Login**

-   **Endpoint**: `POST /api/login`
-   **Body**:
    ```json
    {
        "email": "your_email@example.com",
        "password": "your_password"
    }
    ```
-   **Response**: Returns an access token for authentication.

#### **Get User Details**

-   **Endpoint**: `GET /api/user`
-   **Headers**: Include `Authorization: Bearer <token>`.

---

### **Auction Management**

#### **Create Auction**

-   **Endpoint**: `POST /api/auctions`
-   **Body**:
    ```json
    {
        "title": "Auction Title",
        "description": "Auction Details",
        "auction_date": "YYYY-MM-DD",
        "bid_starting_price": 200,
        "team_balance": 10000,
        "min_bid_increase_amount": 50,
        "min_player_amount": 10
    }
    ```
-   **Response**: Returns created auction details.

#### **Get Auctions**

-   **Endpoint**: `GET /api/auctions`
-   **Response**: List of all auctions.

#### **Get Auction By ID**

-   **Endpoint**: `GET /api/auctions/{id}`
-   **Response**: Details of the specified auction.

#### **Update Auction**

-   **Endpoint**: `PUT /api/auctions/{id}`
-   **Body**: Similar to "Create Auction".
-   **Response**: Updated auction details.

#### **Delete Auction**

-   **Endpoint**: `DELETE /api/auctions/{id}`
-   **Response**: Confirmation message.

#### **Start Auction**

-   **Endpoint**: `POST /api/auctions/{id}/start`
-   **Response**: Starts the auction and returns current auction state.

---

### **Team Management**

#### **Create Team**

-   **Endpoint**: `POST /api/teams`
-   **Body**:
    ```json
    {
        "name": "Team Name",
        "aid": "Auction ID"
    }
    ```
-   **Response**: Returns created team details.

#### **Get Teams**

-   **Endpoint**: `GET /api/teams?auction_id={id}`
-   **Response**: List of teams associated with the auction.

#### **Update Team**

-   **Endpoint**: `PUT /api/teams/{id}`
-   **Body**: Can update attributes like `logo_url`.

#### **Delete Team**

-   **Endpoint**: `DELETE /api/teams/{id}`
-   **Response**: Confirmation message.

---

### **Player Management**

#### **Get All Players**

-   **Endpoint**: `GET /api/players?auctionId={id}`
-   **Response**: List of players in the specified auction.

#### **Get Team Players**

-   **Endpoint**: `GET /api/teams/{team_id}/players`
-   **Response**: Players associated with the team.

---

### **Auction Actions**

#### **Place Bid**

-   **Endpoint**: `POST /api/auctions/{id}/bid?fix={amount}`
-   **Response**: Updates the current bid and state.

#### **Get Auction State**

-   **Endpoint**: `GET /api/auctions/{id}/state`
-   **Response**: Details of the ongoing auction state.

#### **Next Player**

-   **Endpoint**: `POST /api/auctions/{id}/next`
-   **Response**: Moves the auction to the next player or completes the auction.
