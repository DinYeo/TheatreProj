# xampp mysql crash recovery guide

## cause of the crash
when windows shuts down unexpectedly or xampp is abruptly closed, mariadb's aria storage engine and internal system tables (`c:\xampp\mysql\data\mysql`) get corrupted. mariadb starts up, creates the socket, but immediately aborts because it cannot open `mysql.plugin`.

## solution (safe restore without losing database data)

1. stop mysql in xampp control panel.
2. in `c:\xampp\mysql\`:
   - rename the corrupted `data` folder to `data_corrupt_[timestamp]`.
   - copy the `backup` folder and rename the copy to `data`.
3. copy your project database folder and data file from the corrupt folder to the new `data` folder:
   - copy `data_corrupt_[timestamp]\apex_theatre` to `data\apex_theatre`
   - copy `data_corrupt_[timestamp]\ibdata1` to `data\ibdata1`
4. start mysql in xampp control panel.

all tables, shows, seats, and bookings will be fully preserved.

