/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   crypt_func.c                                       :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/12/05 15:09:23 by tktorza           #+#    #+#             */
/*   Updated: 2017/12/05 17:11:13 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../includes/woody.h"

char *ft_nimp(char *key, int nb)
{
	int size = ft_strlen(key);
	int c;
	char *str;
	int i;
	
	if (nb == 0)
	{
		i = 1;
		if ((str = (char *)malloc(sizeof(char) * (size + 1))) == NULL)
			return (NULL);
		str[0] = key[size / 3];
		while (i < size)
		{
			str[i] = key[i - 1] + 3;
			i++;
		}
	}
	else if (nb == 2)
	{
		if ((str = (char *)malloc(sizeof(char) * ((size * 3) + 1))) == NULL)
			return (NULL);
		// i = 2;
		// str[2] = key[size / 3];
		str[0] = key[size / 3];
		i = 1;
		while (i < size * 3)
		{
			c = key[i % size];
			str[i] = (i < size) ?  (char)(c - 15) : (char)(c + 43);
			i++;
		}
	}
	str[i] = '\0';
	return (str);
}

int	ft_strchr_index(const char *s, int c)
{
	int i;

	i = 0;
	while (s && s[i] && s[i] != '\0')
	{
		if (s[i] == c)
			return (i);
		i++;
	}
	if (!c && s != NULL && s[i] == '\0')
		return (i);
	return (0);
}

unsigned long long		ft_atoi_hexa(char *nb)
{
	int		size = ft_strlen(nb) - 1;
	unsigned long long		res = 0;
	unsigned long long 	fact = 1;
	char	c[16] = "0123456789abcdef";

// printf("test ft_atoi_hex\n\n%s\n", nb);
	while (size > -1)
	{
		// printf("\tnb[size] = %c == %d * %d \n", nb[size], ft_strchr_index(c, nb[size]), fact);
		res += (ft_strchr_index(c, nb[size]) * fact);
		size--;
		fact *= 16;
	}
	return (res);
}

unsigned long long		decrypt_key(char *key)
{
	char *new = ft_strsub(key, 6, 6);
	unsigned long long k = ft_atoi_hexa(new);
	printf("key = %s | str = %s --> dec = %llu = %llu\n", key, new, k, ft_atoi_hexa(new));
	free(new);
	return (k);
}

char	*create_key(Elf64_Ehdr *header)
{
	char *value;
	char **desassembly;
	char *fake_start;
	char *key;
	Elf64_Shdr *section = (void *)header + header->e_shoff;	
	unsigned long long rand_start = (&section[header->e_shnum % 3].sh_entsize);

	desassembly = (char **)malloc(sizeof(char *) * 4);
	value =  ft_itoa_base(rand_start / 1000, 16);
	desassembly[1] = (char *)malloc(ft_strlen(value));	
	ft_strcpy(desassembly[1], value);
	printf("value = (d) %llu = (hex) %s\n\n", rand_start / 1000, value);
	//depart a 7 à tj checker 6 char
	desassembly[0] = ft_nimp(value, 0);
	fake_start = ft_nimp(desassembly[0], 2);
	desassembly[2] = (char *)malloc(ft_strlen(fake_start));
	ft_strcpy(desassembly[2], fake_start);
	free(fake_start);
	free(value);

	int size = 0;
	for (int v = 0;v < 3;v++)
	{
		size += ft_strlen(desassembly[v]);
	}
	key = malloc(sizeof(char) * (size + 1));
	int x = 0;
	for (int v = 0;v < 3;v++)
	{
		for (int y = 0; y < ft_strlen(desassembly[v]);y++)
		{
			key[x] = desassembly[v][y];
			x++;
		}
		free(desassembly[v]);
	}
	free(desassembly);
	return (key);
}

char    *crypt_text_section(Elf64_Ehdr *header, Elf64_Shdr *bin_text)
{
    char *key = create_key(header);
    uint8_t *data = (void *)header;
    unsigned long long val_key = decrypt_key(key);
    int diff = 0;
    uint8_t start = data[bin_text->sh_offset];

    printf("val_key = %llu | data = (%d / %c) -->", val_key, (int)data[bin_text->sh_offset], data[bin_text->sh_offset]);
    while (val_key + data[bin_text->sh_offset] > 253)
        val_key /= 10;
    start += (uint8_t)val_key;
    // start /= 2;
    printf("start = %d  --> ", start, val_key);
    // start *= 2;
    start -= (uint8_t)val_key;
    printf("start = %d\n", start, val_key);
    
    for (size_t k = bin_text->sh_offset; k < bin_text->sh_offset + bin_text->sh_size;k++)
    {
        diff = (int)data[k -1];
        diff += (int)data[k];
        printf("\tdata[k] (%d / %c) -->", (int)data[k], data[k]);
        data[k] = (diff <= 255) ? (uin8_t)diff : data[k - 1];


        data[k] = (data[k] * val_key) / 255;
        printf(" (%d / %c) -->", (int)data[k], data[k]);
        data[k] = ((data[k] * 255) / val_key);
        printf(" (%d / %c)\n", (int)data[k], data[k]);
    }

    return (key);
}