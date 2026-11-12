INSERT INTO seats (perf_id, seat_number, status) VALUES
-- Row A
(1, 'A1', 'available'), (1, 'A2', 'available'), (1, 'A3', 'available'), (1, 'A4', 'available'), (1, 'A5', 'available'),
(1, 'A6', 'available'), (1, 'A7', 'available'), (1, 'A8', 'available'), (1, 'A9', 'available'), (1, 'A10', 'available'),
-- Row B
(1, 'B1', 'available'), (1, 'B2', 'available'), (1, 'B3', 'available'), (1, 'B4', 'available'), (1, 'B5', 'available'),
(1, 'B6', 'available'), (1, 'B7', 'available'), (1, 'B8', 'available'), (1, 'B9', 'available'), (1, 'B10', 'available'),
-- Row C
(1, 'C1', 'available'), (1, 'C2', 'available'), (1, 'C3', 'available'), (1, 'C4', 'available'), (1, 'C5', 'available'),
(1, 'C6', 'available'), (1, 'C7', 'available'), (1, 'C8', 'available'), (1, 'C9', 'available'), (1, 'C10', 'available'),
-- Row D
(1, 'D1', 'available'), (1, 'D2', 'available'), (1, 'D3', 'available'), (1, 'D4', 'available'), (1, 'D5', 'available'),
(1, 'D6', 'available'), (1, 'D7', 'available'), (1, 'D8', 'available'), (1, 'D9', 'available'), (1, 'D10', 'available'),
-- Row E
(1, 'E1', 'available'), (1, 'E2', 'available'), (1, 'E3', 'available'), (1, 'E4', 'available'), (1, 'E5', 'available'),
(1, 'E6', 'available'), (1, 'E7', 'available'), (1, 'E8', 'available'), (1, 'E9', 'available'), (1, 'E10', 'available');


=============================================================================================================
""" 
Insert whatever the SELECT returns ->
"""
INSERT INTO seats (perf_id, seat_number, status)
SELECT p.perf_id, CONCAT(r.row_letter, n.seat_num), 'available'
FROM performances p
JOIN (
    SELECT 'A' AS row_letter UNION SELECT 'B' UNION SELECT 'C' UNION SELECT 'D' UNION SELECT 'E'
) r
JOIN (
    SELECT 1 AS seat_num UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
    UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10
) n
WHERE p.perf_id != 1;