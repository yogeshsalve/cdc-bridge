class BaseCDCConnector:
    def __init__(self, config):
        self.config = config
        self.conn = None

    def connect(self):
        raise NotImplementedError

    def poll_changes(self):
        raise NotImplementedError

    def close(self):
        if self.conn:
            self.conn.close()
