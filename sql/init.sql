CREATE TABLE SupportTeam (

    teamCode NUMBER,
    country VARCHAR(20) NOT NULL,
    city VARCHAR(20) NOT NULL,

    PRIMARY KEY (teamCode)

);

CREATE TABLE "User" (

    phoneNumber NUMBER,
    firstName VARCHAR(20) NOT NULL,
    lastName VARCHAR(20) NOT NULL,

    PRIMARY KEY (phoneNumber)

);

CREATE TABLE Admin (

    agentNumber NUMBER,
    firstName VARCHAR(20) NOT NULL,
    lastName VARCHAR(20) NOT NULL,
    worksFor NUMBER NOT NULL,

    PRIMARY KEY (agentNumber),
    FOREIGN KEY (worksFor) REFERENCES SupportTeam(teamCode) ON DELETE CASCADE

);

CREATE TABLE Account (

    username VARCHAR(20),
    dateCreated DATE NOT NULL,
    ownersPhoneNumber NUMBER NOT NULL,
    adminsNumber NUMBER NOT NULL,

    PRIMARY KEY (username),
    FOREIGN KEY (ownersPhoneNumber) REFERENCES "User"(phoneNumber) ON DELETE CASCADE,
    FOREIGN KEY (adminsNumber) REFERENCES Admin(agentNumber) ON DELETE CASCADE

);

CREATE TABLE Mailbox (

    mailboxID NUMBER,
    ownersUsername VARCHAR(50) NOT NULL,

    PRIMARY KEY (mailboxID),
    FOREIGN KEY (ownersUsername) REFERENCES Account(username) ON DELETE CASCADE

);

CREATE TABLE CustomMailbox (

    mailboxID NUMBER,
    ownersUsername VARCHAR(50) NOT NULL,
    dateCreated DATE NOT NULL,
    customLabel VARCHAR(20) NOT NULL,

    PRIMARY KEY (mailboxID),
    FOREIGN KEY (ownersUsername) REFERENCES Account(username) ON DELETE CASCADE

);

CREATE TABLE Email (

    emailID NUMBER,
    sender VARCHAR(20) NOT NULL,
    recipient VARCHAR(20) NOT NULL,
    body VARCHAR(1000) NOT NULL,
    mailboxID NUMBER NOT NULL,

    PRIMARY KEY (emailID),
    FOREIGN KEY (sender) REFERENCES Account(username) ON DELETE CASCADE,
    FOREIGN KEY (recipient) REFERENCES Account(username) ON DELETE CASCADE,
    FOREIGN KEY (mailboxID) REFERENCES Mailbox(mailboxID) ON DELETE CASCADE

);

CREATE TABLE Attachment (

    fileName VARCHAR(50),
    emailID NUMBER NOT NULL,
    fileType VARCHAR(5) NOT NULL,

    PRIMARY KEY (fileName, emailID),
    FOREIGN KEY (emailID) REFERENCES Email(emailID) ON DELETE CASCADE

);

CREATE TABLE Contact (

    emailAddress VARCHAR(50),
    firstName VARCHAR(20) NOT NULL,
    lastName VARCHAR(20) NOT NULL,

    PRIMARY KEY (emailAddress)

);

CREATE TABLE UserContacts (

    userContactID NUMBER,
    usersPhoneNumber NUMBER NOT NULL,
    contactsEmailAddress VARCHAR(50) NOT NULL,

    PRIMARY KEY (userContactID),
    FOREIGN KEY (usersPhoneNumber) REFERENCES "User"(phoneNumber) ON DELETE CASCADE,
    FOREIGN KEY (contactsEmailAddress) REFERENCES Contact(emailAddress) ON DELETE CASCADE

);





INSERT INTO SupportTeam VALUES (1, 'Canada', 'Toronto');
INSERT INTO SupportTeam VALUES (2, 'Canada', 'Vancouver');
INSERT INTO SupportTeam VALUES (3, 'Canada', 'Montreal');
INSERT INTO SupportTeam VALUES (4, 'United States', 'New York');
INSERT INTO SupportTeam VALUES (5, 'United States', 'Los Angeles');

INSERT INTO "User" VALUES (7786652266, 'Siddharth', 'Nand');
INSERT INTO "User" VALUES (4432348866, 'Michael', 'Perkins');
INSERT INTO "User" VALUES (3441128899, 'Saif', 'Karnawi');
INSERT INTO "User" VALUES (5557771111, 'Jennifer', 'Smith');
INSERT INTO "User" VALUES (6668882222, 'Lora', 'Hill');

INSERT INTO Admin VALUES (1010, 'Dave', 'Colby', 1);
INSERT INTO Admin VALUES (1011, 'Emma', 'Stome', 1);
INSERT INTO Admin VALUES (1012, 'Jessica', 'Khali', 2);
INSERT INTO Admin VALUES (1013, 'Jone', 'Doe', 3);
INSERT INTO Admin VALUES (1014, 'Jane', 'Doe', 4);

INSERT INTO Account VALUES ('siddharthnand', TO_DATE('2019-01-01', 'YYYY-MM-DD'), 7786652266, 1010);
INSERT INTO Account VALUES ('michaelperkins', TO_DATE('2019-01-01', 'YYYY-MM-DD'), 4432348866, 1011);
INSERT INTO Account VALUES ('saifkarnawi', TO_DATE('2019-01-01', 'YYYY-MM-DD'), 3441128899, 1012);
INSERT INTO Account VALUES ('jennifersmith', TO_DATE('2019-01-01', 'YYYY-MM-DD'), 5557771111, 1013);
INSERT INTO Account VALUES ('lorahill', TO_DATE('2019-01-01', 'YYYY-MM-DD'), 6668882222, 1014);
INSERT INTO Account VALUES ('k_smith', TO_DATE('2019-01-01', 'YYYY-MM-DD'), 6668882222, 1014);

INSERT INTO Mailbox VALUES (1, 'siddharthnand');
INSERT INTO Mailbox VALUES (2, 'michaelperkins');
INSERT INTO Mailbox VALUES (3, 'saifkarnawi');
INSERT INTO Mailbox VALUES (4, 'jennifersmith');
INSERT INTO Mailbox VALUES (5, 'lorahill');
INSERT INTO Mailbox VALUES (6, 'k_smith');

INSERT INTO CustomMailbox VALUES (1, 'siddharthnand', TO_DATE('2019-01-01', 'YYYY-MM-DD'), 'Important');
INSERT INTO CustomMailbox VALUES (2, 'michaelperkins', TO_DATE('2019-01-01', 'YYYY-MM-DD'), 'Work');
INSERT INTO CustomMailbox VALUES (3, 'saifkarnawi', TO_DATE('2019-01-01', 'YYYY-MM-DD'), 'Family');
INSERT INTO CustomMailbox VALUES (4, 'jennifersmith', TO_DATE('2019-01-01', 'YYYY-MM-DD'), 'Friends');
INSERT INTO CustomMailbox VALUES (5, 'lorahill', TO_DATE('2019-01-01', 'YYYY-MM-DD'), 'Important');
INSERT INTO CustomMailbox VALUES (6, 'k_smith', TO_DATE('2019-01-01', 'YYYY-MM-DD'), 'Junk');

INSERT INTO Email VALUES (1, 'siddharthnand', 'michaelperkins', 'Hello, how are you?', 1);
INSERT INTO Email VALUES (2, 'michaelperkins', 'siddharthnand', 'I am good, how are you?', 2);
INSERT INTO Email VALUES (3, 'saifkarnawi', 'jennifersmith', 'Can we have a meeting tomorrow at 5pm?', 3);
INSERT INTO Email VALUES (4, 'jennifersmith', 'saifkarnawi', 'Can we delay it till next week?', 4);
INSERT INTO Email VALUES (5, 'lorahill', 'k_smith', 'I am going to be late for dinner tonight', 5);
INSERT INTO Email VALUES (6, 'k_smith', 'lorahill', 'Ok, I will wait for you', 6);
INSERT INTO Email VALUES (7, 'siddharthnand', 'lorahill', 'You did not get the job offer', 1);

INSERT INTO Attachment VALUES ('attachment1', 1, 'pdf');
INSERT INTO Attachment VALUES ('attachment2', 2, 'doc');
INSERT INTO Attachment VALUES ('attachment3', 3, 'html');
INSERT INTO Attachment VALUES ('attachment4', 4, 'zip');
INSERT INTO Attachment VALUES ('attachment5', 5, 'pdf');

INSERT INTO Contact VALUES ('siddharthnand@mail.com', 'Siddharth', 'Nand');
INSERT INTO Contact VALUES ('michaelperkins@mail.com', 'Michael', 'Perkins');
INSERT INTO Contact VALUES ('saifkarnawi@mail.com', 'Saif', 'Karnawi');
INSERT INTO Contact VALUES ('nand_s@gmail.com', 'Siddharth', 'Nand');
INSERT INTO Contact VALUES ('jennifersmith@mail.com', 'Jennifer', 'Smith');
INSERT INTO Contact VALUES ('admin@ubc.ca', 'UBC', 'Admin');

INSERT INTO UserContacts VALUES (1, 7786652266, 'michaelperkins@mail.com');
INSERT INTO UserContacts VALUES (2, 7786652266, 'saifkarnawi@mail.com');
INSERT INTO UserContacts VALUES (3, 7786652266, 'nand_s@gmail.com');
INSERT INTO UserContacts VALUES (4, 3441128899, 'admin@ubc.ca');
INSERT INTO UserContacts VALUES (5, 3441128899, 'jennifersmith@mail.com');