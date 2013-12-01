/**
 * Sent sms log table
 */
CREATE TABLE sms_log (
    uuid                CHAR(25)            NOT NULL,

    -- Sent time
    st                  DATETIME            NOT NULL,
    -- Category of msg
    cat                 INTEGER             NOT NULL DEFAULT 0,
    -- Total dest number count
    cnt_dest            INTEGER             NOT NULL DEFAULT 0,
    -- Dest count of China Mobile
    cnt_dest_cm         INTEGER             NOT NULL DEFAULT 0,
    -- Dest count of China Unicom
    cnt_dest_cu         INTEGER             NOT NULL DEFAULT 0,
    -- Dest count of China Telecom
    cnt_dest_ct         INTEGER             NOT NULL DEFAULT 0,
    -- Dest phone numbers
    dest                TEXT                NOT NULL,
    -- Will sms split to N part to send
    cnt_part            INTEGER             NOT NULL DEFAULT 0,
    -- Msg
    sms                 TEXT                NOT NULL,

    ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (uuid),
    INDEX idx_sms_log_1 (st)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
