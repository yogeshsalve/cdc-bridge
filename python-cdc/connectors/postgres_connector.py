import psycopg2
from connectors.base_connector import BaseCDCConnector

class PostgresConnector(BaseCDCConnector):
    def connect(self):
        self.conn = psycopg2.connect(
            host=self.config['host'],
            port=self.config['port'],
            user=self.config['username'],
            password=self.config['password'],
            dbname=self.config['database']
        )
        return True

    def poll_changes(self):
        cur = self.conn.cursor()
        cur.execute("SELECT * FROM some_table ORDER BY id DESC LIMIT 1;")  # demo
        result = cur.fetchall()
        return result
