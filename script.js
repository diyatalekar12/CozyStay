// Fetch hotels data and populate the hotels section
function fetchHotels() {
    fetch("http://localhost/DBMS%20Project/backend/api/getHotels.php")
        .then(response => response.json())
        .then(data => {
            const hotelsContainer = document.getElementById("hotels-container");
            hotelsContainer.innerHTML = ''; // Clear existing data

            if (data.length > 0) {
                data.forEach(hotel => {
                    const hotelDiv = document.createElement("div");
                    hotelDiv.classList.add("hotel");

                    hotelDiv.innerHTML = `
                        <h3>${hotel.name}</h3>
                        <p>Location: ${hotel.location}</p>
                        <p>Starting Price: ₹${hotel.starting_price}</p>
                        <p>Description: ${hotel.description}</p>
                        <img src="${hotel.image_path}" alt="${hotel.name}" width="300px" />
                    `;

                    hotelsContainer.appendChild(hotelDiv);
                    // Populate hotel dropdown in booking form
                    const hotelSelect = document.getElementById("hotel");
                    const option = document.createElement("option");
                    option.value = hotel.id;
                    option.textContent = hotel.name;
                    hotelSelect.appendChild(option);
                });
            } else {
                hotelsContainer.innerHTML = '<p>No hotels available.</p>';
            }
        })
        .catch(err => console.error('Error fetching hotels:', err));
}

// Fetch rooms for a selected hotel and populate the room dropdown
function fetchRooms(hotelId) {
    fetch(`http://localhost/DBMS%20Project/backend/api/getRooms.php?hotel_id=${hotelId}`)
        .then(response => response.json())
        .then(data => {
            const roomSelect = document.getElementById("room-type");
            roomSelect.innerHTML = ''; // Clear existing options

            if (data.length > 0) {
                data.forEach(room => {
                    const option = document.createElement("option");
                    option.value = room.id;
                    option.textContent = `${room.type} - ₹${room.price}`;
                    roomSelect.appendChild(option);
                });
            } else {
                roomSelect.innerHTML = '<option>No rooms available.</option>';
            }
        })
        .catch(err => console.error('Error fetching rooms:', err));
}

// Event listener for hotel selection to update room options
document.getElementById("hotel").addEventListener("change", (e) => {
    const hotelId = e.target.value;
    if (hotelId) {
        fetchRooms(hotelId); // Fetch rooms for the selected hotel
    }
});

// Fetch hotels data when the page loads
window.onload = function() {
    fetchHotels();
};
