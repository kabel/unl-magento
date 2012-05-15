CALL fetch_state_rates(null);
CALL fetch_city_rates(null);
CALL fetch_county_rates(null);
CALL fetch_exempt_rates();
CALL fetch_city_plus_rates('Lincoln', 'Restaurant', 0.02, null);
CALL fetch_force_state_rate(null);
CALL fetch_force_city_rate('Lincoln', null);
CALL fetch_force_city_plus_rate('Lincoln', 'Restaurant', 0.02);
