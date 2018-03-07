/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   crypt_func.c                                       :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/12/05 15:09:23 by tktorza           #+#    #+#             */
/*   Updated: 2017/12/08 17:02:02 by tktorza          ###   ########.fr       */
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

uint8_t		ft_atoi_bit(char *nb)
{
	uint8_t fact = 1;
	uint8_t	res = 0;
	int	size = ft_strlen(nb) - 1;

	while (size > -1)
	{
		res += (nb[size] == '1') ? fact : 0;
		fact *= 2;
		size--;
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

char	*ft_itoa_bit(uint8_t nb)
{
	char	*str = (char *)malloc(sizeof(char) * 9);
	if (!str)
		return (NULL);
	int		index = 7;

	str[8] = '\0';
	while (index > -1)
	{
		str[index] = ((nb % 2) + 48);
		index--;
		nb /= 2;
	}
	return (str);
}

//reverse the bit index with this next one
uint8_t		reverse_bit_index(uint8_t nb, int index)
{
	char *bit = ft_itoa_bit(nb);
	char prev;
	int sign = (index < 0) ? -1 : 1;
	// fprintf(stderr, "\t\t%d in bit = %s --> ", nb, bit);
	
	index = (index < 0) ? -index : index;
	index = 7 - index;
	if (sign == -1)
	{
		prev = bit[index + 1];
		bit[index + 1] = bit[index];
		bit[index] = prev;
	}
	else
	{
		prev = bit[index - 1];
		bit[index - 1] = bit[index];
		bit[index] = prev;	
	}
	// fprintf(stderr, " %s\n", bit);
	
	return (ft_atoi_bit(bit));
}

char	*decrypt_text_section(Elf64_Ehdr *header, Elf64_Shdr *bin_text, char *key)
{
	unsigned long long val_key = decrypt_key(key);
	uint8_t *data = (void *)header;

	while (val_key + data[bin_text->sh_offset] > 253)
		val_key /= 10;

	int index = val_key % 7;
	printf("True\n");
	int sign = 1;
	// for (int x =bin_text->sh_offset;x < bin_text->sh_offset + bin_text->sh_size;x++)
	// {
	// 	printf("%d ", data[x]);
	// }
	// printf("\nasm : (%d) offset(%d) size (%d) (%d) (%d)\n", data[bin_text->sh_offset], bin_text->sh_size, index, bin_text->sh_offset, val_key);
	printf("\nasmTOOL : (%d) ++--> (%d)\n", &data[bin_text->sh_offset], data[bin_text->sh_offset + 1]);
	// printf("Prev: %d %d %d %d %d\n", 	data[bin_text->sh_offset], index, bin_text->sh_offset + bin_text->sh_size, bin_text->sh_offset, val_key);
	/*

		decrypt_true(
		data,
		index,
		bin_text->sh_offset + bin_text->sh_size,
		bin_text->sh_offset,
		val_key
		);
		decrypt_true = for(int i = 0;i<12;i++){
			putchar(str[i]);
		}
		*/
		//*data + bin_text->sh_offset = data[bin_text->sh_offset]
		char str[56] = "abcdefghijklmnopqrstuvwxyx";
		
		decrypt_true(str, 12, index, bin_text->sh_offset, val_key);
	//a recup
		// decrypt_true(
		// data,
		// /*bin_text->sh_offset + */bin_text->sh_size,
		// index,
		// bin_text->sh_offset,
		// val_key
		// );
		//a remettre preceemment
	printf("Hello\n");

	/*
	for (size_t k = bin_text->sh_offset; k < bin_text->sh_offset + bin_text->sh_size;k++)
    {
        data[k] = reverse_bit_index(data[k], index * sign);
		if ((k - bin_text->sh_offset) % val_key == 0)
			index = val_key % 7;
		else
		{
			if (sign > 0)
			{
				//on esssayer d'incrementer index
				if (index < 6)
					index++;
				else
					sign = -1;
			}
			else
			{
				if (index > 1)
					index--;
				else
					sign = 1;
			}
		}
	}
*/
	return ((char *)&data[bin_text->sh_offset]);
}

// void	test_decrypt(Elf64_Ehdr *header, Elf64_Shdr *bin_text, char *key)
// {
//     uint8_t *data = (void *)header;
// 	char *decrypt = decrypt_text_section(header, bin_text, key);
// 	int i = 0;

// 	for (size_t k = bin_text->sh_offset; k < bin_text->sh_offset + bin_text->sh_size;k++)
// 	{
// 		if (decrypt[i] != data[k])
// 			printf("\t\t\t--------------->FALSE decrypt(%d) != data(%d)\n", decrypt[i], data[k]);
// 		i++;
// 	}
// }

// int					ft_strcmp_size(const char *s1, const char *s2, int size)
// {
// 	unsigned int	i;

// 	i = 0;
// 	while ( i < size && s1[i] == s2[i] && s1[i] && s2[i])
// 		i++;
// 	if (s1[i] == s2[i])
// 		return (0);
// 	else
// 		return (i);
// }

char    *crypt_text_section(Elf64_Ehdr *header, Elf64_Shdr *bin_text)
{
    char *key = create_key(header);
    uint8_t *data = (void *)header;
	unsigned long long val_key = decrypt_key(key);
	
    while (val_key + data[bin_text->sh_offset] > 253)
        val_key /= 10;
	
		int index = val_key % 7;
		int sign = 1;
    for (size_t k = bin_text->sh_offset; k < bin_text->sh_offset + bin_text->sh_size;k++)
    {
        data[k] = reverse_bit_index(data[k], index * sign);
		if ((k - bin_text->sh_offset) % val_key == 0)
			index = val_key % 7;
		else
		{
			if (sign > 0)
			{
				//on esssayer d'incrementer index
				if (index < 6)
					index++;
				else
					sign = -1;
			}
			else
			{
				if (index > 1)
					index--;
				else
					sign = 1;
			}
		}
	}
    return (key);
}