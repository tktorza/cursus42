# Libft - 42 - Project 01

## Partie 1

#### memset
> void *memset(void *s, int c, size_t n);
* The memset() function fills the first n bytes of the memory area pointed to by s with the constant byte c.
* The memset() function returns a pointer to the memory area s.

#### bzero
> void bzero(void *s, size_t n);
* The bzero() function sets the first n bytes of the area starting at s to zero (bytes containing aq\0aq).
* None.

#### memcpy
> void *memcpy(void *dest, const void *src, size_t n);
* The memcpy() function copies n bytes from memory area src to memory area dest.
The memory areas must not overlap. Use memmove(3) if the memory areas do overlap.
* The memcpy() function returns a pointer to dest.

#### memccpy
> void *memccpy(void *dest, const void *src, int c, size_t n);
* The memccpy() function copies no more than n bytes from memory area src to memory area dest,
stopping when the character c is found. If the memory areas overlap, the results are undefined.
* The memccpy() function returns a pointer to the next character in dest after c,
or NULL if c was not found in the first n characters of src.

#### memmove
> void *memmove(void *dest, const void *src, size_t n);
* The memmove() function copies n bytes from memory area src to memory area dest.
The memory areas may overlap: copying takes place as though the bytes in src are first copied into a
temporary array that does not overlap src or dest, and the bytes are then copied from the temporary array to dest.
* The memmove() function returns a pointer to dest.

#### memchr
> void *memchr(const void *s, int c, size_t n);
* The memchr() function scans the initial n bytes of the memory area pointed to by s for the first instance of c.
Both c and the bytes of the memory area pointed to by s are interpreted as unsigned char.
* The memchr() and memrchr() functions return a pointer to the matching byte or NULL
if the character does not occur in the given memory area.

#### memcmp
> int memcmp(const void *s1, const void *s2, size_t n);
* The memcmp() function compares the first n bytes (each interpreted as unsigned char) of the memory areas s1 and s2.
* The memcmp() function returns an integer less than, equal to, or greater than zero if the first n bytes of s1 is found,
respectively, to be less than, to match, or be greater than the first n bytes of s2.
For a nonzero return value, the sign is determined by the sign of the difference between the first pair of bytes
(interpreted as unsigned char) that differ in s1 and s2.

#### strlen
> size_t strlen(const char *s);
* The strlen() function calculates the length of the string s, excluding the terminating null byte (aq\0aq).
* The strlen() function returns the number of bytes in the string s.

#### strdup
> char *strdup(const char *s);
* The strdup() function returns a pointer to a new string which is a duplicate of the string s.
Memory for the new string is obtained with malloc(3), and can be freed with free(3).
* The strdup() function returns a pointer to the duplicated string, or NULL if insufficient memory was available.

#### strcpy
> char *strcpy(char *dest, const char *src);
* The strcpy() function copies the string pointed to by src, including the terminating null byte ('\0'),
to the buffer pointed to by dest. The strings may not overlap, and the destination string dest must be
large enough to receive the copy. Beware of buffer overruns!
* The strcpy() and strncpy() functions return a pointer to the destination string dest.

#### strncpy
> char *strncpy(char *dest, const char *src, size_t n);
* The strncpy() function is similar, except that at most n bytes of src are copied. Warning: If there is no null byte
among the first n bytes of src, the string placed in dest will not be null-terminated. If the length of src is less
than n, strncpy() writes additional null bytes to dest to ensure that a total of n bytes are written.
* The strcpy() and strncpy() functions return a pointer to the destination string dest.

#### strcat
> char *strcat(char *dest, const char *src);
* The strcat() function appends the src string to the dest string, overwriting the terminating null byte ('\0')
at the end of dest, and then adds a terminating null byte. The strings may not overlap, and the dest string
must have enough space for the result. If dest is not large enough, program behavior is unpredictable;
buffer overruns are a favorite avenue for attacking secure programs.
* The strcat() and strncat() functions return a pointer to the resulting string dest.

#### strncat
> char *strncat(char *dest, const char *src, size_t n);
* The strncat() function appends not more than n characters from s2, and then adds a terminating `\0'.
The source and destination strings should not overlap, as the behavior is undefined.
* The strcat() and strncat() functions return a pointer to the resulting string dest.

#### strlcat
> size_t strlcat(char *dst, const char *src, size_t size);
* The strlcpy() and strlcat() functions copy and concatenate strings respectively. They are designed to be safer,
more consistent, and less error prone replacements for strncpy(3) and strncat(3). Unlike those functions,
strlcpy() and strlcat() take the full size of the buffer (not just the length) and guarantee to NUL-terminate
the result (as long as size is larger than 0 or, in the case of strlcat(), as long as there is at least one
byte free in dst). Note that a byte for the NUL should be included in size. Also note that strlcpy() and
strlcat() only operate on true ''C'' strings. This means that for strlcpy() src must be NUL-terminated
and for strlcat() both src and dst must be NUL-terminated. The strlcat() function appends the NUL-terminated string src
to the end of dst. It will append at most size - strlen(dst) - 1 bytes, NUL-terminating the result.
* The strlcpy() and strlcat() functions return the total length of the string they tried to create. For strlcpy()
that means the length of src. For strlcat() that means the initial length of dst plus the length of src.
While this may seem somewhat confusing, it was done to make truncation detection simple.
Note, however, that if strlcat() traverses size characters without finding a NUL, the length of the string is
considered to be size and the destination string will not be NUL-terminated (since there was no space for the NUL).
This keeps strlcat() from running off the end of a string. In practice this should not happen (as it means that
either size is incorrect or that dst is not a proper ''C'' string). The check exists to prevent potential security
problems in incorrect code.

#### strchr
> char *strchr(const char *s, int c);
* The strchr() function returns a pointer to the first occurrence of the character c in the string s.
* The strchr() and strrchr() functions return a pointer to the matched character or NULL if the character is not found. The terminating null byte is considered part of the string, so that if c is specified as aq\0aq, these functions return a pointer to the terminator.

#### strrchr
> char *strrchr(const char *s, int c);
* The strrchr() function returns a pointer to the last occurrence of the character c in the string s.
* The strchr() and strrchr() functions return a pointer to the matched character or NULL if the character is not found. The terminating null byte is considered part of the string, so that if c is specified as aq\0aq, these functions return a pointer to the terminator.

#### strstr
> char *strstr(const char *haystack, const char *needle);
* The strstr() function finds the first occurrence of the substring needle in the string haystack. The terminating null bytes (aq\0aq) are not compared.
* These functions return a pointer to the beginning of the substring, or NULL if the substring is not found.

#### strnstr
> char *strnstr(const char	*big, const char *little, size_t len);
* The strnstr() function locates the	first occurrence of the	null-terminated string little in the string big,
where not more than	len characters are searched. Characters that appear after a `\0'	character are not searched.
Since the strnstr() function is	a FreeBSD specific API,	it should only be used when portability is not a concern.
* If little is an empty string, big is returned; if little occurs nowhere in	big, NULL is returned; otherwise a pointer to the first	character of the first occurrence of little is returned.

#### strcmp
> int strcmp(const char *s1, const char *s2);
* The strcmp() function compares the two strings s1 and s2. It returns an integer less than, equal to, or greater than zero if s1 is found, respectively, to be less than, to match, or be greater than s2.
* The strcmp() and strncmp() functions return an integer less than, equal to, or greater than zero if s1 (or the first n bytes thereof) is found, respectively, to be less than, to match, or be greater than s2.

#### strncmp
> int strncmp(const char *s1, const char *s2, size_t n);
* The strncmp() function is similar, except it only compares the first (at most) n bytes of s1 and s2.
* The strcmp() and strncmp() functions return an integer less than, equal to, or greater than zero if s1 (or the first n bytes thereof) is found, respectively, to be less than, to match, or be greater than s2.

#### atoi
> int atoi(const char *nptr);
* The atoi() function converts the initial portion of the string pointed to by nptr to int.
* The converted value.

#### isalpha
> int isalpha(int c);
* Checks for an alphabetic character; in the standard "C" locale, it is equivalent to (isupper(c) || islower(c)). In some locales, there may be additional characters for which isalpha() is true-letters which are neither upper case nor lower case.
* The values returned are nonzero if the character c falls into the tested class, and a zero value if not.

#### isdigit
> int isdigit(int c);
* Checks for a digit (0 through 9).
* The values returned are nonzero if the character c falls into the tested class, and a zero value if not.

#### isalnum
> int isalnum(int c);
* Checks for an alphanumeric character; it is equivalent to (isalpha(c) || isdigit(c)).
* The values returned are nonzero if the character c falls into the tested class, and a zero value if not.

#### isascii
> int isascii(int c);
* Checks whether c is a 7-bit unsigned char value that fits into the ASCII character set.
* The values returned are nonzero if the character c falls into the tested class, and a zero value if not.

#### isprint
> int isprint(int c);
* Checks for any printable character including space.
* The values returned are nonzero if the character c falls into the tested class, and a zero value if not.

#### toupper
> int toupper(int c);
* toupper() converts the letter c to upper case, if possible. If c is not an unsigned char value, or EOF, the behavior of these functions is undefined.
* The value returned is that of the converted letter, or c if the conversion was not possible.

#### tolower
> int tolower(int c);
* tolower() converts the letter c to lower case, if possible. If c is not an unsigned char value, or EOF, the behavior of these functions is undefined.
* The value returned is that of the converted letter, or c if the conversion was not possible.

### Ascii Tombstone
