CALL fetch_state_rates();
CALL fetch_city_rates();
CALL fetch_county_rates();
CALL fetch_exempt_rates();
CALL fetch_city_plus_rates('Lincoln', 'Restaurant', 0.02);
CALL fetch_force_state_rate();
CALL fetch_force_city_rate('Lincoln');
CALL fetch_force_city_plus_rate('Lincoln', 'Restaurant', 0.02);
