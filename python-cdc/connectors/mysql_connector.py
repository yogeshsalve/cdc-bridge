import mysql.connector
from connectors.base_connector import BaseCDCConnector

class MySQLConnector(BaseCDCConnector):
    def connect(self):
        self.conn = mysql.connector.connect(
            host=self.config['host'],
            port=self.config['port'],
            user=self.config['username'],
            password=self.config['password'],
            database=self.config['database']
        )
        return True

    def poll_changes(self):
        cur = self.conn.cursor(dictionary=True)
        cur.execute("SELECT * FROM some_table ORDER BY id DESC LIMIT 1;")  # demo
        result = cur.fetchall()
        return result
